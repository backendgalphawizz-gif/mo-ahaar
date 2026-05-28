<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DeliveryAssignment;
use App\Models\DeliveryAssignmentInvite;
use App\Models\DeliveryAssignmentRejection;
use App\Services\DriverAssignmentService;
use Illuminate\Http\Request;

class DeliveryController extends DriverAppController
{
    public function __construct(
        private readonly DriverAssignmentService $assignmentService
    ) {
    }

    public function accept(Request $request, int $assignmentId)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $assignment = DeliveryAssignment::with(['order.customer.user'])->find($assignmentId);
        if (!$assignment) {
            return response()->json([
                'status' => false,
                'message' => 'Delivery assignment not found.',
            ], 404);
        }

        try {
            $assignment = $this->assignmentService->acceptByDriver($assignment, $driver);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Delivery accepted successfully',
            'data' => [
                'delivery' => $this->formatAssignmentItem($assignment),
            ],
        ], 200);
    }

    public function reject(Request $request, int $assignmentId)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $assignment = DeliveryAssignment::with(['order.customer.user'])->find($assignmentId);
        if (!$assignment) {
            return response()->json([
                'status' => false,
                'message' => 'Delivery assignment not found.',
            ], 404);
        }

        if ($assignment->status === DeliveryAssignment::STATUS_NEW && $assignment->driver_id === null) {
            DeliveryAssignmentRejection::firstOrCreate(
                [
                    'assignment_id' => $assignment->assignment_id,
                    'driver_id' => $driver->user_id,
                ],
                ['reason' => $validated['reason'] ?? null]
            );

            if ($assignment->assignment_mode === DeliveryAssignment::MODE_BROADCAST) {
                DeliveryAssignmentInvite::where('assignment_id', $assignment->assignment_id)
                    ->where('driver_id', $driver->user_id)
                    ->where('status', DeliveryAssignmentInvite::STATUS_PENDING)
                    ->update(['status' => DeliveryAssignmentInvite::STATUS_DECLINED]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Delivery rejected successfully',
                'data' => [
                    'delivery' => $this->formatAssignmentItem($assignment),
                ],
            ], 200);
        }

        if (
            $assignment->status === DeliveryAssignment::STATUS_ASSIGNED
            && (int) $assignment->driver_id === (int) $driver->user_id
        ) {
            $assignment->status = DeliveryAssignment::STATUS_REJECTED_BY_DRIVER;
            $assignment->reject_reason = $validated['reason'] ?? null;
            $assignment->driver_id = null;
            $assignment->assigned_at = null;
            $assignment->save();

            return response()->json([
                'status' => true,
                'message' => 'Delivery rejected successfully',
                'data' => [
                    'delivery' => $this->formatAssignmentItem($assignment),
                ],
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'You cannot reject this delivery.',
        ], 422);
    }
}
