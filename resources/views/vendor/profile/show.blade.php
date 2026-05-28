@extends('layouts.app')

@section('content')
@include('admin.partials.dashboard-ui')
<div class="page-body">
    <div class="container-fluid">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">Vendor Profile</h5>
                <small class="text-muted">Manage your profile and business information</small>
            </div>
            @if(!$edit)
                <a href="{{ route('vendor.profile', ['tab' => $tab, 'edit' => 1]) }}" class="btn btn-sm btn-brown">Edit Profile</a>
            @else
                <div class="d-flex gap-2">
                    <a href="{{ route('vendor.profile', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
                    <button form="vendorProfileForm" class="btn btn-sm btn-brown">Save Changes</button>
                </div>
            @endif
        </div>

        <div class="card dashboard-card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ !empty($vendor->profile_image) ? asset('public/uploads/vendors/'.$vendor->profile_image) : asset('public/assets/images/users/4.jpg') }}" style="width:56px;height:56px;border-radius:50%;object-fit:cover;" alt="profile">
                    <div>
                        <h6 class="mb-0">{{ $vendor->owner_name ?: ($user->name ?? 'Vendor') }}</h6>
                        <small class="text-muted">{{ $vendor->business_name ?: '-' }}</small><br>
                        <small class="text-muted">{{ $vendor->email ?: ($user->email ?? '-') }}</small>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-3">
                    @foreach(['personal' => 'Personal Details', 'business' => 'Business Details', 'bank' => 'Bank Details', 'documents' => 'Documents'] as $key => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('vendor.profile', ['tab' => $key, 'edit' => $edit ? 1 : 0]) }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>

                <form id="vendorProfileForm" method="POST" enctype="multipart/form-data" action="{{ route('vendor.profile.update') }}">
                    @csrf
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="row g-3">
                        @if($tab === 'personal')
                            <div class="col-md-6"><label class="form-label">Full Name</label><input class="form-control" name="owner_name" value="{{ old('owner_name', $vendor->owner_name ?: $user->name) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-md-6"><label class="form-label">Email Address</label><input class="form-control" name="email" value="{{ old('email', $vendor->email ?: $user->email) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-md-6"><label class="form-label">Mobile Number</label><input class="form-control" name="mobile" value="{{ old('mobile', $vendor->mobile ?: $user->mobile) }}" {{ $edit ? '' : 'readonly' }}></div>
                            @if($edit)<div class="col-md-6"><label class="form-label">Profile Image</label><input type="file" class="form-control" name="profile_image"></div>@endif
                        @elseif($tab === 'business')
                            <div class="col-md-6"><label class="form-label">Business Name</label><input class="form-control" name="business_name" value="{{ old('business_name', $vendor->business_name) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-md-6"><label class="form-label">Business Mobile</label><input class="form-control" name="business_phone" value="{{ old('business_phone', $vendor->business_phone) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-12"><label class="form-label">Business Email</label><input class="form-control" name="business_email" value="{{ old('business_email', $vendor->business_email) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address" value="{{ old('address', $vendor->address) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="business_description" {{ $edit ? '' : 'readonly' }}>{{ old('business_description', $vendor->business_description) }}</textarea></div>
                            <div class="col-md-6"><label class="form-label">PAN Number</label><input class="form-control" name="pan_number" value="{{ old('pan_number', $vendor->pan_number) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-md-6"><label class="form-label">GST Number</label><input class="form-control" name="gst_number" value="{{ old('gst_number', $vendor->gst_number) }}" {{ $edit ? '' : 'readonly' }}></div>
                        @elseif($tab === 'bank')
                            <div class="col-md-6"><label class="form-label">Bank Name</label><input class="form-control" name="bank_name" value="{{ old('bank_name', $vendor->bank_name) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-md-6"><label class="form-label">Branch Name</label><input class="form-control" name="branch_name" value="{{ old('branch_name', $vendor->branch_name) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-12"><label class="form-label">Account Type</label><input class="form-control" name="account_type" value="{{ old('account_type', $vendor->account_type) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-12"><label class="form-label">Account Number</label><input class="form-control" name="bank_account" value="{{ old('bank_account', $vendor->bank_account) }}" {{ $edit ? '' : 'readonly' }}></div>
                            <div class="col-12"><label class="form-label">IFSC Code</label><input class="form-control" name="ifsc_code" value="{{ old('ifsc_code', $vendor->ifsc_code) }}" {{ $edit ? '' : 'readonly' }}></div>
                        @else
                            <div class="col-12">
                                @foreach(['aadhaar_card' => 'Aadhar Card', 'pan_card' => 'PAN Card'] as $field => $label)
                                    <div class="p-3 mb-2 rounded border bg-light">
                                        <strong>{{ $label }}</strong><br>
                                        <small class="text-muted">Uploaded on: {{ optional($vendor->updated_at)->format('F d, Y') }}</small><br>
                                        @if(!empty($vendor->{$field}))
                                            <a class="btn btn-sm btn-outline-primary mt-2" target="_blank" href="{{ asset('public/uploads/vendors/documents/'.$vendor->{$field}) }}">View Document</a>
                                        @endif
                                        @if($edit)
                                            <input type="file" class="form-control mt-2" name="{{ $field }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.btn-brown{background:#8a3f00;border-color:#8a3f00;color:#fff}
.btn-brown:hover{background:#733400;border-color:#733400;color:#fff}
.nav-tabs .nav-link{border:0;color:#64748b;font-size:12px}
.nav-tabs .nav-link.active{border:0;border-bottom:2px solid #ef4444;color:#111827;font-weight:600}
</style>
@endsection

