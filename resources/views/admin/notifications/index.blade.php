@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex align-items-center mb-4">
            <h5 class="mb-0">{{ $title }}</h5>
        </div>

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

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Total Sent</small>
                            <h3 class="mb-0">{{ number_format((int) ($totalSent ?? 0)) }}</h3>
                        </div>
                        <span class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center stats-icon">
                            <i class="ri-notification-3-line"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">This Week</small>
                            <h3 class="mb-0">{{ number_format((int) ($sentThisWeek ?? 0)) }}</h3>
                        </div>
                        <span class="rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center stats-icon">
                            <i class="ri-notification-2-line"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Today</small>
                            <h3 class="mb-0">{{ number_format((int) ($sentToday ?? 0)) }}</h3>
                        </div>
                        <span class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center stats-icon">
                            <i class="ri-notification-badge-line"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-card mb-4">
            <div class="card-body">
                <h6 class="mb-3">Send Notifications</h6>
                <form method="POST" action="{{ route('admin.notifications.store') }}" class="row g-3">
                    @csrf
                    <input type="hidden" name="recipient_scope" value="all">
                    <input type="hidden" name="title" value="Admin Notification">
                    <input type="hidden" name="target_type" id="target_type" value="{{ old('target_type', 'users') }}">

                    <div class="col-12">
                        <div class="audience-switch">
                            <button type="button" class="audience-btn active" data-type="users"><i class="ri-user-line me-1"></i>Users</button>
                            <button type="button" class="audience-btn" data-type="drivers"><i class="ri-truck-line me-1"></i>Delivery Partners</button>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notification Message</label>
                        <textarea name="message" rows="2" class="form-control @error('message') is-invalid @enderror" placeholder="Enter your message..." required>{{ old('message') }}</textarea>
                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-theme px-4"><i class="ri-send-plane-line me-1"></i>Send</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <h6 class="mb-3">Recent Notifications</h6>
                <div class="d-flex flex-column gap-2">
                    @forelse($notifications as $notification)
                        @php
                            $audience = $notification->target_type === 'drivers' ? 'Delivery Partners' : 'All Users';
                        @endphp
                        <div class="border rounded p-3 d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">{{ $audience }}</div>
                                <small class="text-muted">{{ $notification->message }}</small>
                            </div>
                            <small class="text-muted">{{ optional($notification->created_at)->format('Y-m-d h:i A') }}</small>
                        </div>
                    @empty
                        <div class="text-muted">No notifications sent yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const targetType = document.getElementById('target_type');
    const buttons = document.querySelectorAll('.audience-btn');
    function setAudience(type) {
        targetType.value = type;
        buttons.forEach(function (btn) {
            btn.classList.toggle('active', btn.getAttribute('data-type') === type);
        });
    }
    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setAudience(btn.getAttribute('data-type'));
        });
    });
    setAudience(targetType.value || 'users');
});
</script>
<style>
.stats-icon { width: 40px; height: 40px; }
.audience-switch { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; background: #f1f5f9; border-radius: 999px; padding: 3px; }
.audience-btn { border: 0; background: transparent; border-radius: 999px; padding: 8px 10px; font-size: 13px; }
.audience-btn.active { background: #ffffff; box-shadow: 0 1px 2px rgba(15, 23, 42, .08); font-weight: 600; }
</style>
@endsection
