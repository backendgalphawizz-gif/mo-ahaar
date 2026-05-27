@extends('layouts.app')

@section('content')
    <div class="page-body">
        <div class="container-fluid">
            <div class="title-header option-title d-flex align-items-center mb-4">
                <h5><i class="ri-user-settings-line me-2"></i>Profile Setting</h5>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    <div class="col-xl-4 col-lg-5">
                        <div class="card h-100 profile-summary-card">
                            <div class="card-body text-center">
                                <div class="profile-avatar mx-auto mb-3">
                                    @if(!empty($admin->profile_image))
                                        <img src="{{ asset('public/uploads/admins/' . $admin->profile_image) }}"
                                            alt="{{ $admin->name }}" class="profile-avatar-image">
                                    @else
                                        {{ strtoupper(substr((string) ($admin->name ?? 'A'), 0, 1)) }}
                                    @endif
                                </div>
                                <h5 class="mb-1">{{ $admin->name }}</h5>
                                <p class="text-muted mb-2">{{ $admin->email }}</p>
                                <span class="badge badge-light-success rounded-pill px-3 py-2">Administrator</span>

                                <div class="mt-3 text-start">
                                    <label class="form-label">Profile Photo</label>
                                    <input type="file" name="profile_image"
                                        class="form-control @error('profile_image') is-invalid @enderror"
                                        accept=".jpg,.jpeg,.png,.webp">
                                    @error('profile_image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Upload JPG, PNG, or WEBP up to 2MB.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8 col-lg-7">
                        <div class="card mb-4">
                            <div class="card-header card-header-2">
                                <h5><i class="ri-profile-line me-2"></i>Profile Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" value="{{ old('name', $admin->name) }}"
                                            class="form-control @error('name') is-invalid @enderror" data-alpha-name
                                            required>
                                        @error('name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email ID</label>
                                        <div class="position-relative">
                                            <input type="email" value="{{ $admin->email }}"
                                                class="form-control  pe-5" readonly disabled>
                                            <span class="profile-lock-icon"><i class="ri-lock-line"></i></span>
                                        </div>
                                        <small class="text-muted">Email ID cannot be updated. It is unique for the admin
                                            account.</small>
                                    </div>
                                    @if(isset($admin->mobile) || \Illuminate\Support\Facades\Schema::hasColumn('users', 'mobile'))
                                        <div class="col-md-6">
                                            <label class="form-label">Mobile No.</label>
                                            <input type="text" name="mobile" value="{{ old('mobile', $admin->mobile ?? '') }}"
                                                class="form-control" maxlength="10" inputmode="numeric" pattern="[0-9]{10}"
                                                placeholder="Example: 9876543210"
                                                title="Enter a 10-digit mobile number (e.g., 9876543210)"
                                                oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                                            <small class="text-muted">Accepted format: 10 digits only (example:
                                                9876543210).</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header card-header-2">
                                <h5><i class="ri-lock-password-line me-2"></i>Update Password</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" name="current_password"
                                            class="form-control @error('current_password') is-invalid @enderror"
                                            autocomplete="current-password">
                                        @error('current_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="new_password"
                                            class="form-control @error('new_password') is-invalid @enderror"
                                            autocomplete="new-password">
                                        @error('new_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation"
                                            class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                            autocomplete="new-password">
                                        @error('new_password_confirmation')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <p class="text-muted small mt-3 mb-0">Leave password fields blank if you do not want to
                                    change the password.</p>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-theme">Update Profile</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <style>
        .profile-summary-card {
            border: 1px solid #ebeff4;
            background: radial-gradient(circle at top right, rgb(213 124 34 / 16%), #fff 60%);
        }

        .profile-avatar {
            width: 94px;
            height: 94px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #0f4c75, #3282b8);
            overflow: hidden;
        }

        .profile-avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-lock-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7c8798;
            font-size: 18px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var nameInput = document.querySelector('input[name="name"][data-alpha-name]');
            if (!nameInput) {
                return;
            }

            function sanitizeName(value) {
                return value.replace(/[^a-zA-Z\s]/g, '').replace(/\s{2,}/g, ' ');
            }

            nameInput.addEventListener('input', function () {
                this.value = sanitizeName(this.value);
            });

            nameInput.addEventListener('paste', function () {
                var input = this;
                setTimeout(function () {
                    input.value = sanitizeName(input.value);
                }, 0);
            });
        });
    </script>
@endsection