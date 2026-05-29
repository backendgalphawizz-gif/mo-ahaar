<div class="modal fade" id="assignDriverModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="assignDriverForm" action="#" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Delivery Boy — <span id="assignDriverOrderLabel"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Select Driver <span class="text-danger">*</span></label>
                    <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror">
                        <option value="">Choose driver...</option>
                        @foreach($driversList as $d)
                            <option value="{{ $d->user_id }}" @selected(old('driver_id') == $d->user_id)>{{ $d->name }}@if(!empty($d->mobile)) (+91 {{ $d->mobile }})@endif</option>
                        @endforeach
                    </select>
                    @include('admin.partials.field-error', ['field' => 'driver_id'])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
