@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @include('admin.partials.figma-page-header', [
            'title' => 'Push Notifications',
            'subtitle' => 'Send and manage alerts to your platform users',
        ])

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fix the highlighted fields and submit again.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card dashboard-card mb-4">
            <div class="card-body">
                <h6 class="figma-section-title">Send New Notification</h6>
                <form method="POST" action="{{ route('admin.notifications.store') }}" class="row g-3">
                    @csrf
                    <input type="hidden" name="recipient_scope" value="all">
                    <input type="hidden" name="target_type" id="target_type" value="{{ old('target_type', 'users') }}">

                    <div class="col-md-4">
                        <label class="form-label">User Type <span class="text-danger">*</span></label>
                        <select id="user_type_select" class="form-select @error('target_type') is-invalid @enderror">
                            <option value="users" {{ old('target_type', 'users') === 'users' ? 'selected' : '' }}>Customers</option>
                            <option value="drivers" {{ old('target_type') === 'drivers' ? 'selected' : '' }}>Delivery Boys</option>
                        </select>
                        @error('target_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Notification Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Enter notification title" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Message Body <span class="text-danger">*</span></label>
                        <textarea name="message" rows="3" class="form-control @error('message') is-invalid @enderror" placeholder="Write your notification message..." required>{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_banner" disabled>
                            <label class="form-check-label text-muted" for="include_banner">Include Image Banner (coming soon)</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-theme"><i class="ri-send-plane-line me-1"></i>Send Notification</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern align-middle">
                        <thead>
                            <tr>
                                <th>Sl. No.</th>
                                <th>Title &amp; Media</th>
                                <th>User Type</th>
                                <th>Message Summary</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notification)
                                @php
                                    $userType = match ($notification->target_type) {
                                        'drivers' => 'Delivery',
                                        'vendors' => 'Vendor',
                                        default => 'User',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $notifications->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $notification->title ?: 'Notification' }}</div>
                                        <small class="text-muted">{{ optional($notification->created_at)->format('d/m/Y h:i A') }}</small>
                                    </td>
                                    <td>{{ $userType }}</td>
                                    <td>{{ Str::limit($notification->message, 80) }}</td>
                                    <td>
                                        <span class="text-muted small">Sent</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No notifications sent yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($notifications->hasPages())
                    <div class="mt-3">{{ $notifications->links('pagination::bootstrap-5') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var targetType = document.getElementById('target_type');
    var userTypeSelect = document.getElementById('user_type_select');
    if (!targetType || !userTypeSelect) return;

    userTypeSelect.addEventListener('change', function () {
        targetType.value = this.value;
    });
    targetType.value = userTypeSelect.value;
});
</script>
@endsection
