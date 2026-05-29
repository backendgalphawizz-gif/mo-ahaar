<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DeliveryAssignment;
use App\Models\DeliveryAssignmentRejection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends DriverAppController
{
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

        if ($assignment->status !== DeliveryAssignment::STATUS_NEW || $assignment->driver_id !== null) {
            return response()->json([
                'status' => false,
                'message' => 'This delivery has already been assigned to another driver',
            ], 422);
        }

        $alreadyRejected = DeliveryAssignmentRejection::where('assignment_id', $assignmentId)
            ->where('driver_id', $driver->user_id)
            ->exists();

        if ($alreadyRejected) {
            return response()->json([
                'status' => false,
                'message' => 'You have already declined this delivery',
            ], 422);
        }

        DB::transaction(function () use ($assignment, $driver) {
            $assignment->driver_id = $driver->user_id;
            $assignment->status = DeliveryAssignment::STATUS_ASSIGNED;
            $assignment->assigned_at = now();
            $assignment->save();

            NotificationController::notify(
                (int) $driver->user_id,
                'New Delivery Assigned',
                'You accepted order ' . ($assignment->order?->order_number ?? $assignment->order_id) . '.',
                'new_delivery_assigned',
                $assignment
            );
        });

        $assignment->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Delivery accepted successfully! You can now start the pickup',
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
            'message' => 'You cannot decline this delivery at this stage',
        ], 422);
    }
}
