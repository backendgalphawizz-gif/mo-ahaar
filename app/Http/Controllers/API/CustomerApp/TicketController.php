<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\Users;
use App\Repositories\TicketRepository;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function __construct(
        private TicketRepository $tickets,
        private TicketService $ticketService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof Users || !$user->isCustomerAppUser()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $tickets = $this->tickets->forUser((int) $user->user_id, min(max((int) $request->query('per_page', 15), 1), 50));

        return response()->json([
            'status' => true,
            'message' => 'Tickets retrieved successfully',
            'data' => [
                'tickets' => collect($tickets->items())->map(fn (Ticket $ticket) => $this->formatTicket($ticket))->values(),
                'current_page' => $tickets->currentPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'last_page' => $tickets->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof Users || !$user->isCustomerAppUser()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(Ticket::typeOptions())],
            'subject' => ['required', 'string', 'max:190'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['nullable', Rule::in(Ticket::priorityOptions())],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx,txt,webp', 'max:5120'],
        ]);

        $ticket = $this->ticketService->createTicket($user, $validated, $request->file('attachments', []));

        return response()->json([
            'status' => true,
            'message' => 'Ticket created successfully',
            'data' => [
                'ticket' => $this->formatTicket($ticket),
            ],
        ], 201);
    }

    public function reply(Request $request, int $ticketId): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof Users || !$user->isCustomerAppUser()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $ticket = $this->tickets->findForUser($ticketId, (int) $user->user_id);
        if (!$ticket) {
            return response()->json(['status' => false, 'message' => 'Ticket not found'], 404);
        }

        if ($ticket->status === Ticket::STATUS_CLOSED) {
            return response()->json(['status' => false, 'message' => 'Closed tickets cannot be replied to.'], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,txt,webp', 'max:5120'],
        ]);

        $reply = $this->ticketService->addReply($ticket, $user, $validated['message'], false, $request->file('attachment'), false);
        $ticket = $this->tickets->findForUser($ticketId, (int) $user->user_id) ?? $ticket;

        return response()->json([
            'status' => true,
            'message' => 'Reply added successfully',
            'data' => [
                'reply' => $this->formatReply($reply),
                'ticket' => $this->formatTicket($ticket),
            ],
        ]);
    }

    private function formatTicket(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'type' => $ticket->type,
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'assigned_to' => $ticket->assigned_to,
            'assigned_to_name' => $ticket->assignedTo?->name,
            'attachments' => $ticket->attachments->map(fn ($attachment) => [
                'id' => $attachment->id,
                'file_path' => $attachment->file_path,
                'file_url' => $this->ticketService->ticketUrl($attachment->file_path),
            ])->values(),
            'replies' => $ticket->replies
                ->where('is_internal', false)
                ->values()
                ->map(fn (TicketReply $reply) => $this->formatReply($reply)),
            'created_at' => optional($ticket->created_at)->toDateTimeString(),
            'updated_at' => optional($ticket->updated_at)->toDateTimeString(),
        ];
    }

    private function formatReply(TicketReply $reply): array
    {
        return [
            'id' => $reply->id,
            'message' => $reply->message,
            'is_admin' => (bool) $reply->is_admin,
            'attachment' => $reply->attachment,
            'attachment_url' => $this->ticketService->ticketUrl($reply->attachment),
            'user' => [
                'user_id' => $reply->user?->user_id,
                'name' => $reply->user?->name,
            ],
            'created_at' => optional($reply->created_at)->toDateTimeString(),
        ];
    }
}