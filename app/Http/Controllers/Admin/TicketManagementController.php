<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Users;
use App\Repositories\TicketRepository;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketManagementController extends Controller
{
    public function __construct(
        private TicketRepository $tickets,
        private TicketService $ticketService,
    ) {
    }

    public function index(Request $request)
    {
        $title = 'Support Tickets';
        $filters = [
            'status' => $request->query('status'),
            'type' => $request->query('type'),
            'user_id' => $request->query('user_id'),
            'search' => $request->query('search'),
        ];

        $tickets = $this->tickets->adminList($filters, 15);
        $customers = Users::query()
            ->where('role_type', Users::CUSTOMER_APP_ROLE_TYPE)
            ->orderBy('name')
            ->get(['user_id', 'name', 'email']);

        return view('admin.tickets.index', compact('title', 'tickets', 'customers'));
    }

    public function show(int $id)
    {
        $title = 'Ticket Details';
        $ticket = $this->tickets->findWithRelations($id);

        if (!$ticket) {
            return redirect()->route('admin.tickets.index')->with('error', 'Ticket not found.');
        }

        $supportAgents = Users::query()
            ->where('role_type', 1)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['user_id', 'name', 'email']);

        return view('admin.tickets.show', compact('title', 'ticket', 'supportAgents'));
    }

    public function reply(Request $request, int $id)
    {
        $ticket = $this->tickets->findWithRelations($id);
        if (!$ticket) {
            return redirect()->route('admin.tickets.index')->with('error', 'Ticket not found.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,txt,webp', 'max:5120'],
        ]);

        $adminUser = $this->resolveAdminUser();
        if (!$adminUser) {
            return back()->with('error', 'Admin user session not found.');
        }

        $this->ticketService->addReply(
            $ticket,
            $adminUser,
            $validated['message'],
            true,
            $request->file('attachment'),
            false
        );

        return back()->with('success', 'Reply added successfully.');
    }

    public function update(Request $request, int $id)
    {
        $ticket = $this->tickets->findWithRelations($id);
        if (!$ticket) {
            return redirect()->route('admin.tickets.index')->with('error', 'Ticket not found.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(Ticket::statusOptions())],
            'priority' => ['required', Rule::in(Ticket::priorityOptions())],
            'type' => ['required', Rule::in(Ticket::typeOptions())],
            'assigned_to' => ['nullable', 'integer', 'exists:users,user_id'],
        ]);

        $this->ticketService->updateTicket($ticket, $validated);

        return back()->with('success', 'Ticket updated successfully.');
    }

    public function internalNote(Request $request, int $id)
    {
        $ticket = $this->tickets->findWithRelations($id);
        if (!$ticket) {
            return redirect()->route('admin.tickets.index')->with('error', 'Ticket not found.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx,txt,webp', 'max:5120'],
        ]);

        $adminUser = $this->resolveAdminUser();
        if (!$adminUser) {
            return back()->with('error', 'Admin user session not found.');
        }

        $this->ticketService->addReply(
            $ticket,
            $adminUser,
            $validated['message'],
            true,
            $request->file('attachment'),
            true
        );

        return back()->with('success', 'Internal note saved.');
    }

    private function resolveAdminUser(): ?Users
    {
        $sessionUserId = session('user_id');
        if (!empty($sessionUserId)) {
            return Users::query()->where('user_id', $sessionUserId)->first();
        }

        return Users::query()->where('role_type', 1)->where('status', 1)->orderBy('user_id')->first();
    }
}