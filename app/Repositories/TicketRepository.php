<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TicketRepository
{
    public function adminList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->when(!empty($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(!empty($filters['type']), fn (Builder $query) => $query->where('type', $filters['type']))
            ->when(!empty($filters['user_id']), fn (Builder $query) => $query->where('user_id', (int) $filters['user_id']))
            ->when(!empty($filters['search']), function (Builder $query) use ($filters) {
                $search = trim((string) $filters['search']);
                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery->where('subject', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function forUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findWithRelations(int $ticketId): ?Ticket
    {
        return $this->baseQuery()->where('id', $ticketId)->first();
    }

    public function findForUser(int $ticketId, int $userId): ?Ticket
    {
        return $this->baseQuery()
            ->where('id', $ticketId)
            ->where('user_id', $userId)
            ->first();
    }

    private function baseQuery(): Builder
    {
        return Ticket::with([
            'user',
            'assignedTo',
            'attachments',
            'replies.user',
        ]);
    }
}