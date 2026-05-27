@extends('layouts.app')

@section('content')
<div class="page-body">
    <div class="container-fluid">
        <div class="title-header option-title mb-4 d-flex align-items-center">
            <h5><i class="ri-notification-3-line me-2"></i>{{ $title }}</h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
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
            <div class="col-xl-5">
                <div class="card">
                    <div class="card-header card-header-2">
                        <h5>Send Notification</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.notifications.store') }}" class="row g-3" id="notificationForm">
                            @csrf

                            <div class="col-12">
                                <label class="form-label">Send To <span class="text-danger">*</span></label>
                                <select name="target_type" id="target_type" class="form-select @error('target_type') is-invalid @enderror" required>
                                    <option value="">Select Recipient Type</option>
                                    <option value="users" {{ old('target_type') === 'users' ? 'selected' : '' }}>Users</option>
                                    <option value="vendors" {{ old('target_type') === 'vendors' ? 'selected' : '' }}>Vendors</option>
                                </select>
                                @error('target_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Recipient Scope <span class="text-danger">*</span></label>
                                <select name="recipient_scope" id="recipient_scope" class="form-select @error('recipient_scope') is-invalid @enderror" required>
                                    <option value="all" {{ old('recipient_scope', 'all') === 'all' ? 'selected' : '' }}>All</option>
                                    <option value="specific" {{ old('recipient_scope') === 'specific' ? 'selected' : '' }}>Specific</option>
                                </select>
                                @error('recipient_scope')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 d-none" id="recipientWrap">
                                <label class="form-label">Select Recipient <span class="text-danger">*</span></label>
                                <select name="recipient_id" id="recipient_id" class="form-select @error('recipient_id') is-invalid @enderror">
                                    <option value="">Select Recipient</option>
                                </select>
                                @error('recipient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" maxlength="190" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea name="message" rows="6" class="form-control @error('message') is-invalid @enderror" maxlength="5000" required>{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-theme">Send Notification</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card card-table">
                    <div class="card-header card-header-2 d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Notification History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table all-package theme-table align-middle">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Audience</th>
                                        <th>Recipient</th>
                                        <th>Title</th>
                                        <th>Details</th>
                                        <th>Sent At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($notifications as $notification)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if($notification->target_type === 'users')
                                                    <span class="badge badge-soft-success">Users</span>
                                                @elseif($notification->target_type === 'vendors')
                                                    <span class="badge badge-soft-warning">Vendors</span>
                                                @else
                                                    <span class="badge badge-soft-info">Delivery Partners</span>
                                                @endif
                                            </td>
                                            <td>{{ $notification->recipient_name ?: '-' }}</td>
                                            <td>{{ $notification->title }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary view-notification-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#notificationDetailModal"
                                                    data-title="{{ $notification->title }}"
                                                    data-message="{{ htmlentities($notification->message) }}"
                                                    data-audience="{{ $notification->target_type === 'users' ? 'Users' : ($notification->target_type === 'vendors' ? 'Vendors' : 'Other') }}"
                                                    data-recipient="{{ $notification->recipient_name ?: '-' }}"
                                                    data-date="{{ optional($notification->created_at)->format('d M Y, h:i A') }}"
                                                >View</button>
                                            </td>
                                            <!-- Notification Detail Modal -->
                                            <div class="modal fade" id="notificationDetailModal" tabindex="-1" aria-labelledby="notificationDetailModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content border border-2 rounded-3 shadow">
                                                        <div class="modal-header border-bottom border-2">
                                                            <h5 class="modal-title" id="notificationDetailModalLabel">Notification Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <div class="row mb-3 pb-2 border-bottom">
                                                                <div class="col-sm-3 fw-bold">Title</div>
                                                                <div class="col-sm-9" id="notif-title"></div>
                                                            </div>
                                                            <div class="row mb-3 pb-2 border-bottom">
                                                                <div class="col-sm-3 fw-bold">Audience</div>
                                                                <div class="col-sm-9" id="notif-audience"></div>
                                                            </div>
                                                            <div class="row mb-3 pb-2 border-bottom">
                                                                <div class="col-sm-3 fw-bold">Recipient</div>
                                                                <div class="col-sm-9" id="notif-recipient"></div>
                                                            </div>
                                                            <div class="row mb-3 pb-2 border-bottom">
                                                                <div class="col-sm-3 fw-bold">Message</div>
                                                                <div class="col-sm-9" id="notif-message"></div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-3 fw-bold">Sent At</div>
                                                                <div class="col-sm-9" id="notif-date"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <td>{{ optional($notification->created_at)->format('d M Y, h:i A') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No notifications sent yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $notifications->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.view-notification-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('notif-title').textContent = btn.getAttribute('data-title');
            document.getElementById('notif-audience').textContent = btn.getAttribute('data-audience');
            document.getElementById('notif-recipient').textContent = btn.getAttribute('data-recipient');
            document.getElementById('notif-message').innerHTML = btn.getAttribute('data-message');
            document.getElementById('notif-date').textContent = btn.getAttribute('data-date');
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const targetType = document.getElementById('target_type');
    const recipientScope = document.getElementById('recipient_scope');
    const recipientWrap = document.getElementById('recipientWrap');
    const recipientSelect = document.getElementById('recipient_id');
    const oldRecipientId = "{{ old('recipient_id') }}";

    function isSpecificScope() {
        return recipientScope.value === 'specific';
    }

    function resetRecipientSelect() {
        recipientSelect.innerHTML = '<option value="">Select Recipient</option>';
    }

    function toggleRecipient() {
        if (isSpecificScope()) {
            recipientWrap.classList.remove('d-none');
            recipientSelect.setAttribute('required', 'required');
            loadRecipients();
        } else {
            recipientWrap.classList.add('d-none');
            recipientSelect.removeAttribute('required');
            resetRecipientSelect();
        }
    }

    async function loadRecipients() {
        const type = targetType.value;
        resetRecipientSelect();

        if (!type || !isSpecificScope()) {
            return;
        }

        try {
            const response = await fetch("{{ route('admin.notifications.recipients') }}?type=" + encodeURIComponent(type));
            const data = await response.json();

            (data || []).forEach(function (item) {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.label;
                if (String(oldRecipientId) === String(item.id)) {
                    option.selected = true;
                }
                recipientSelect.appendChild(option);
            });
        } catch (e) {
            console.error('Unable to load recipients', e);
        }
    }

    targetType.addEventListener('change', loadRecipients);
    recipientScope.addEventListener('change', toggleRecipient);

    toggleRecipient();
});
</script>
@endsection
