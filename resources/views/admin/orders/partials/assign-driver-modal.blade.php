<div class="modal fade" id="assignDriverModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="assignDriverForm" action="#">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Delivery Boy — <span id="assignDriverOrderLabel"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Select Driver</label>
                    <select name="driver_id" class="form-select" required>
                        <option value="">Choose driver...</option>
                        @foreach($driversList as $d)
                            <option value="{{ $d->user_id }}">{{ $d->name }}@if(!empty($d->mobile)) (+91 {{ $d->mobile }})@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
