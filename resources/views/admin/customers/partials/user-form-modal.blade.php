{{-- Figma: Add / Edit User modal (Full Name, Email, Phone, Address) --}}
<div class="modal fade moa-user-modal" id="userFormModal" tabindex="-1" aria-labelledby="userFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="userFormModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.store-customer') }}" id="userFormModalForm" novalidate>
                @csrf
                <input type="hidden" name="customer_id" id="userFormCustomerId" value="{{ old('customer_id') }}" disabled>

                <div class="modal-body pt-2">
                    @if($errors->any() && in_array(session('open_user_modal'), ['add', 'edit'], true))
                        <div class="alert alert-danger py-2 small mb-3">Please fix the errors below and try again.</div>
                    @endif

                    <div class="mb-3">
                        <label for="userFormName" class="form-label">Full Name</label>
                        <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="userFormName" name="customer_name"
                               value="{{ old('customer_name') }}" placeholder="Enter full name" maxlength="40" autocomplete="name">
                        @include('admin.partials.field-error', ['field' => 'customer_name'])
                    </div>

                    <div class="mb-3">
                        <label for="userFormEmail" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('customer_email') is-invalid @enderror" id="userFormEmail" name="customer_email"
                               value="{{ old('customer_email') }}" placeholder="Enter email address" autocomplete="email">
                        @include('admin.partials.field-error', ['field' => 'customer_email'])
                    </div>

                    <div class="mb-3">
                        <label for="userFormPhone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" id="userFormPhone" name="customer_phone"
                               value="{{ old('customer_phone') }}" placeholder="10-digit phone number" maxlength="10" inputmode="numeric"
                               pattern="[0-9]{10}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)" autocomplete="tel">
                        @include('admin.partials.field-error', ['field' => 'customer_phone'])
                    </div>

                    <div class="mb-0">
                        <label for="userFormAddress" class="form-label">Address</label>
                        <textarea class="form-control @error('customer_address') is-invalid @enderror" id="userFormAddress" name="customer_address"
                                  rows="3" placeholder="Enter address">{{ old('customer_address') }}</textarea>
                        @include('admin.partials.field-error', ['field' => 'customer_address'])
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-figma-primary" id="userFormSubmitBtn">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>
