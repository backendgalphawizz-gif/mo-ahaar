<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RespondsWithToggleStatus
{
    /**
     * JSON for AJAX toggles; redirect with flash for normal form posts.
     *
     * @param  array<string, mixed>  $jsonPayload
     */
    protected function respondToggleStatus(
        Request $request,
        bool $success,
        array $jsonPayload = [],
        ?string $message = null
    ): JsonResponse|RedirectResponse {
        $message = $message ?? ($success ? 'Status updated successfully.' : 'Could not update status.');

        $payload = array_merge([
            'success' => $success,
            'status' => $success,
            'message' => $message,
        ], $jsonPayload);

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json($payload, $success ? 200 : 422);
        }

        return redirect()->back()->with($success ? 'success' : 'error', $message);
    }
}
