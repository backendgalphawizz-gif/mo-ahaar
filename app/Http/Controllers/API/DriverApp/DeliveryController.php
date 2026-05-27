<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DeliveryAssignment;
use App\Models\DeliveryAssignmentRejection;
use App\Models\DriverNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                'message' => 'This delivery is no longer available to accept.',
            ], 422);
        }

        $alreadyRejected = DeliveryAssignmentRejection::where('assignment_id', $assignmentId)
            ->where('driver_id', $driver->user_id)
            ->exists();

        if ($alreadyRejected) {
            return response()->json([
                'status' => false,
                'message' => 'You have already rejected this delivery.',
            ], 422);
        }

        DB::transaction(function () use ($assignment, $driver) {
            $assignment->driver_id = $driver->user_id;
            $assignment->status = DeliveryAssignment::STATUS_ASSIGNED;
            $assignment->assigned_at = now();
            $assignment->save();

            $this->notifyDriver(
                (int) $driver->user_id,
                'Delivery accepted',
                'You accepted order ' . ($assignment->order?->order_number ?? $assignment->order_id) . '.',
                $assignment
            );
        });

        $assignment->refresh();

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

    private function notifyDriver(int $driverId, string $title, string $message, DeliveryAssignment $assignment): void
    {
        if (!Schema::hasTable('driver_notifications')) {
            return;
        }

        DriverNotification::create([
            'driver_id' => $driverId,
            'title' => $title,
            'message' => $message,
            'type' => 'delivery_update',
            'assignment_id' => $assignment->assignment_id,
            'order_id' => $assignment->order_id,
        ]);
    }
}
