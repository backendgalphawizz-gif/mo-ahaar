@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <h5 class="mb-0">Admin Profile</h5>
            <button type="submit" form="adminProfileForm" class="btn btn-success">Save Changes</button>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card dashboard-card mb-4">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <div class="position-relative">
                    <div class="profile-avatar">
                        @if(!empty($admin->profile_image))
                            <img src="{{ asset('public/uploads/admins/' . $admin->profile_image) }}" alt="" class="profile-avatar-image">
                        @else
                            {{ strtoupper(substr((string) ($admin->name ?? 'A'), 0, 1)) }}
                        @endif
                    </div>
                </div>
                <div>
                    <h5 class="mb-0">{{ $admin->name }}</h5>
                    <p class="text-muted mb-0">{{ $admin->email }}</p>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs admin-profile-tabs mb-4" id="profileTabs">
            <li class="nav-item"><button type="button" class="nav-link active" data-tab="personal">Personal Details</button></li>
            <li class="nav-item"><button type="button" class="nav-link" data-tab="password">Change Password</button></li>
            <li class="nav-item"><button type="button" class="nav-link" data-tab="documents">Vendor Document</button></li>
        </ul>

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" id="adminProfileForm">
            @csrf

            <div class="profile-tab-panel" data-panel="personal">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $admin->name) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" value="{{ $admin->email }}" class="form-control" readonly disabled>
                            </div>
                            @if(\Illuminate\Support\Facades\Schema::hasColumn('users', 'mobile'))
                                <div class="col-md-6">
                                    <label class="form-label">Mobile No.</label>
                                    <input type="text" name="mobile" value="{{ old('mobile', $admin->mobile ?? '') }}" class="form-control" maxlength="10" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                                </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="profile_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-tab-panel d-none" data-panel="password">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" autocomplete="new-password">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0">Leave blank to keep your current password.</p>
                    </div>
                </div>
            </div>

            <div class="profile-tab-panel d-none" data-panel="documents">
                <div class="card dashboard-card">
                    <div class="card-body p-0">
                        <table class="table table-modern mb-0">
                            <thead><tr><th>Document</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td>Aadhaar Card</td>
                                    <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" disabled></div></td>
                                    <td><span class="text-muted small">Upload via store settings</span></td>
                                </tr>
                                <tr>
                                    <td>PAN Card</td>
                                    <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" checked disabled></div></td>
                                    <td><span class="text-muted small">Upload via store settings</span></td>
                                </tr>
                                <tr>
                                    <td>Bank Details</td>
                                    <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" checked disabled></div></td>
                                    <td><span class="text-muted small">Upload via store settings</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<style>
.profile-avatar { width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#fff;background:linear-gradient(135deg,#0f4c75,#3282b8);overflow:hidden; }
.profile-avatar-image { width:100%;height:100%;object-fit:cover; }
#profileTabs .nav-link { cursor:pointer; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#profileTabs .nav-link').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('#profileTabs .nav-link').forEach(function (t) { t.classList.remove('active'); });
            tab.classList.add('active');
            var key = tab.getAttribute('data-tab');
            document.querySelectorAll('.profile-tab-panel').forEach(function (p) {
                p.classList.toggle('d-none', p.getAttribute('data-panel') !== key);
            });
        });
    });
});
</script>
@endsection
