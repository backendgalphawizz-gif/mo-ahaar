<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use App\Models\Users;
use App\Repositories\TicketRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class TicketService
{
    public function __construct(private TicketRepository $tickets)
    {
    }

    public function createTicket(Users $user, array $data, array $attachments = []): Ticket
    {
        $ticket = DB::transaction(function () use ($user, $data, $attachments) {
            $ticket = Ticket::create([
                'user_id' => $user->user_id,
                'type' => $data['type'],
                'subject' => $data['subject'],
                'description' => $data['description'],
                'status' => Ticket::STATUS_OPEN,
                'priority' => $data['priority'] ?? Ticket::PRIORITY_MEDIUM,
                'assigned_to' => $data['assigned_to'] ?? null,
            ]);

            $this->storeAttachments($ticket, $attachments);

            return $ticket;
        });

        $ticket = $this->tickets->findWithRelations((int) $ticket->id) ?? $ticket;
        $this->sendTicketCreatedEmails($ticket);

        return $ticket;
    }

    public function addReply(Ticket $ticket, Users $actor, string $message, bool $isAdmin = false, ?UploadedFile $attachment = null, bool $isInternal = false): TicketReply
    {
        $reply = DB::transaction(function () use ($ticket, $actor, $message, $isAdmin, $attachment, $isInternal) {
            $storedAttachment = $attachment ? $this->storeSingleFile($attachment, 'uploads/tickets/replies') : null;

            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $actor->user_id,
                'message' => $message,
                'is_admin' => $isAdmin,
                'is_internal' => $isInternal,
                'attachment' => $storedAttachment,
            ]);

            if ($isAdmin && !$isInternal && in_array($ticket->status, [Ticket::STATUS_OPEN, Ticket::STATUS_WAITING_ON_USER], true)) {
                $ticket->update(['status' => Ticket::STATUS_IN_PROGRESS]);
            }

            if (!$isAdmin && $ticket->status !== Ticket::STATUS_CLOSED) {
                $ticket->update(['status' => Ticket::STATUS_WAITING_ON_USER]);
            }

            return $reply->load('user');
        });

        if ($isAdmin && !$isInternal) {
            $ticket->refresh();
            $this->sendAdminReplyEmail($ticket, $reply);
        }

        return $reply;
    }

    public function updateTicket(Ticket $ticket, array $data): Ticket
    {
        $originalStatus = $ticket->status;

        $ticket->fill([
            'status' => $data['status'] ?? $ticket->status,
            'priority' => $data['priority'] ?? $ticket->priority,
            'assigned_to' => $data['assigned_to'] ?? $ticket->assigned_to,
            'type' => $data['type'] ?? $ticket->type,
        ]);
        $ticket->save();

        $ticket->load(['user', 'assignedTo', 'attachments', 'replies.user']);

        if ($ticket->status !== $originalStatus) {
            $this->sendStatusChangedEmail($ticket, $originalStatus);
        }

        return $ticket;
    }

    public function ticketUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return url('public/' . ltrim($path, '/'));
    }

    private function storeAttachments(Ticket $ticket, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if (!$attachment instanceof UploadedFile) {
                continue;
            }

            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'file_path' => $this->storeSingleFile($attachment, 'uploads/tickets'),
            ]);
        }
    }

    private function storeSingleFile(UploadedFile $file, string $directory): string
    {
        $targetDirectory = public_path($directory);
        File::ensureDirectoryExists($targetDirectory);

        $fileName = now()->format('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($targetDirectory, $fileName);

        return trim($directory . '/' . $fileName, '/');
    }

    private function sendTicketCreatedEmails(Ticket $ticket): void
    {
        $ownerEmail = $ticket->user?->email;
        if (!empty($ownerEmail)) {
            $this->safeMail($ownerEmail, 'Ticket Created: ' . $ticket->subject, "Your ticket #{$ticket->id} has been created successfully. Current status: {$ticket->status}.");
        }

        $supportEmails = Users::query()
            ->where('role_type', 1)
            ->where('status', 1)
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        foreach ($supportEmails as $email) {
            $this->safeMail((string) $email, 'New Support Ticket #' . $ticket->id, "A new ticket has been created by {$ticket->user?->name}. Subject: {$ticket->subject}");
        }
    }

    private function sendAdminReplyEmail(Ticket $ticket, TicketReply $reply): void
    {
        $ownerEmail = $ticket->user?->email;
        if (empty($ownerEmail)) {
            return;
        }

        $this->safeMail($ownerEmail, 'Admin replied to Ticket #' . $ticket->id, $reply->message);
    }

    private function sendStatusChangedEmail(Ticket $ticket, string $fromStatus): void
    {
        $ownerEmail = $ticket->user?->email;
        if (empty($ownerEmail)) {
            return;
        }

        $body = "Your ticket #{$ticket->id} status changed from {$fromStatus} to {$ticket->status}.";
        $this->safeMail($ownerEmail, 'Ticket Status Updated #' . $ticket->id, $body);
    }

    private function safeMail(string $to, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        } catch (\Throwable) {
        }
    }
}