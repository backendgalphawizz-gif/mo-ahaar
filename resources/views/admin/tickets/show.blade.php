@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <h5 class="mb-0">{{ $title }}</h5>
                <small class="text-muted">Ticket #{{ $ticket->id }} by {{ $ticket->user?->name ?: 'User #' . $ticket->user_id }}</small>
            </div>
            <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm">Back to tickets</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fix the highlighted fields and submit again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Ticket Summary</h6>
                        <div class="mb-2"><strong>Subject:</strong> {{ $ticket->subject }}</div>
                        <div class="mb-2"><strong>Type:</strong> {{ ucfirst($ticket->type) }}</div>
                        <div class="mb-2"><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $ticket->status)) }}</div>
                        <div class="mb-2"><strong>Priority:</strong> {{ ucfirst($ticket->priority) }}</div>
                        <div class="mb-2"><strong>User:</strong> {{ $ticket->user?->name ?: 'User #' . $ticket->user_id }}</div>
                        <div class="mb-2"><strong>Email:</strong> {{ $ticket->user?->email ?: 'N/A' }}</div>
                        <div class="mb-0"><strong>Created:</strong> {{ optional($ticket->created_at)->format('d M Y h:i A') }}</div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Update Ticket</h6>
                        <form method="POST" action="{{ route('admin.tickets.update', $ticket->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label-title">Status</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    @foreach(\App\Models\Ticket::statusOptions() as $status)
                                        <option value="{{ $status }}" {{ old('status', $ticket->status) === $status ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-title">Priority</label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    @foreach(\App\Models\Ticket::priorityOptions() as $priority)
                                        <option value="{{ $priority }}" {{ old('priority', $ticket->priority) === $priority ? 'selected' : '' }}>{{ ucfirst($priority) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-title">Type</label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach(\App\Models\Ticket::typeOptions() as $type)
                                        <option value="{{ $type }}" {{ old('type', $ticket->type) === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-title">Assign To</label>
                                <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                    <option value="">Unassigned</option>
                                    @foreach($supportAgents as $agent)
                                        <option value="{{ $agent->user_id }}" {{ (string) old('assigned_to', $ticket->assigned_to) === (string) $agent->user_id ? 'selected' : '' }}>
                                            {{ $agent->name ?: 'Admin #' . $agent->user_id }}{{ $agent->email ? ' (' . $agent->email . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-theme w-100">Save Ticket</button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Original Attachments</h6>
                        @forelse($ticket->attachments as $attachment)
                            <div class="mb-2">
                                <a href="{{ url('public/' . $attachment->file_path) }}" target="_blank" rel="noopener">{{ basename($attachment->file_path) }}</a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No ticket attachments uploaded.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Description</h6>
                        <p class="mb-0" style="white-space: pre-line;">{{ $ticket->description }}</p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Conversation</h6>
                        @forelse($ticket->replies->where('is_internal', false) as $reply)
                            <div class="border rounded p-3 mb-3 {{ $reply->is_admin ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                    <div>
                                        <strong class="text-dark">{{ $reply->user?->name ?: 'User #' . $reply->user_id }}</strong>
                                        <span class="badge {{ $reply->is_admin ? 'bg-primary' : 'bg-secondary' }} ms-2">{{ $reply->is_admin ? 'Admin' : 'User' }}</span>
                                    </div>
                                    <small class="text-muted ">{{ optional($reply->created_at)->format('d M Y h:i A') }}</small>
                                </div>
                                <div style="white-space: pre-line;" class="text-dark">{{ $reply->message }}</div>
                                @if($reply->attachment)
                                    <div class="mt-2">
                                        <a href="{{ url('public/' . $reply->attachment) }}" target="_blank" rel="noopener">View attachment</a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">No replies yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Reply to Ticket</h6>
                        <form method="POST" action="{{ route('admin.tickets.reply', $ticket->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror" placeholder="Write your response" required>{{ old('message') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                                <small class="text-muted">Allowed: jpg, png, webp, pdf, doc, docx, txt. Max 5MB.</small>
                            </div>
                            <button type="submit" class="btn btn-theme">Send Reply</button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3 text-muted">Internal Notes</h6>
                        @forelse($ticket->replies->where('is_internal', true) as $reply)
                            <div class="border rounded p-3 mb-3 bg-warning bg-opacity-10">
                                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                                    <strong>{{ $reply->user?->name ?: 'User #' . $reply->user_id }}</strong>
                                    <small class="text-muted">{{ optional($reply->created_at)->format('d M Y h:i A') }}</small>
                                </div>
                                <div style="white-space: pre-line;">{{ $reply->message }}</div>
                                @if($reply->attachment)
                                    <div class="mt-2">
                                        <a href="{{ url('public/' . $reply->attachment) }}" target="_blank" rel="noopener">View attachment</a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted">No internal notes yet.</p>
                        @endforelse

                        <form method="POST" action="{{ route('admin.tickets.internal-note', $ticket->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <textarea name="message" rows="4" class="form-control" placeholder="Add an internal note" required></textarea>
                            </div>
                            <div class="mb-3">
                                <input type="file" name="attachment" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-outline-dark">Save Internal Note</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection