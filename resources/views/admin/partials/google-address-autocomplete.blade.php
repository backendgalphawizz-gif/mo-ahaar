@php
    $googleMapsApiKey = (string) config('services.google_maps.api_key', '');
    $addressInputId = $addressInputId ?? 'vendor_business_address';
    $latInputId = $latInputId ?? 'vendor_latitude';
    $lngInputId = $lngInputId ?? 'vendor_longitude';
@endphp

<style>
    .pac-container { z-index: 10050 !important; }
</style>

<script>
function initAdminVendorBusinessAddressAutocomplete() {
    var addressInput = document.getElementById(@json($addressInputId));
    var latInput = document.getElementById(@json($latInputId));
    var lngInput = document.getElementById(@json($lngInputId));
    if (!addressInput || !latInput || !lngInput) {
        return;
    }
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        var help = document.getElementById('vendor-address-maps-help');
        if (help) {
            help.classList.remove('d-none');
        }
        return;
    }

    var autocomplete = new google.maps.places.Autocomplete(addressInput, {
        fields: ['formatted_address', 'geometry'],
        types: ['address'],
    });

    autocomplete.addListener('place_changed', function () {
        var place = autocomplete.getPlace();
        if (!place || !place.geometry || !place.geometry.location) {
            latInput.value = '';
            lngInput.value = '';
            return;
        }
        addressInput.value = place.formatted_address || addressInput.value;
        latInput.value = place.geometry.location.lat().toFixed(7);
        lngInput.value = place.geometry.location.lng().toFixed(7);
        addressInput.classList.remove('is-invalid');
    });

    addressInput.addEventListener('input', function () {
        if (document.activeElement === addressInput) {
            latInput.value = '';
            lngInput.value = '';
        }
    });

    var form = document.getElementById('vendorWizardForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            var tabInput = form.querySelector('input[name="tab"]');
            if (!tabInput || tabInput.value !== 'business') {
                return;
            }
            var actionInput = e.submitter && e.submitter.name === 'wizard_action' ? e.submitter.value : '';
            if (actionInput !== 'next' && actionInput !== 'submit') {
                return;
            }
            if (!addressInput.value.trim()) {
                return;
            }
            if (!latInput.value || !lngInput.value) {
                e.preventDefault();
                addressInput.classList.add('is-invalid');
                var msg = document.getElementById('vendor-address-coords-error');
                if (msg) {
                    msg.classList.remove('d-none');
                }
                addressInput.focus();
            }
        });
    }
}
</script>

@if($googleMapsApiKey !== '')
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initAdminVendorBusinessAddressAutocomplete"></script>
@else
<script>
document.addEventListener('DOMContentLoaded', function () {
    var help = document.getElementById('vendor-address-maps-help');
    if (help) {
        help.classList.remove('d-none');
    }
    if (typeof initAdminVendorBusinessAddressAutocomplete === 'function') {
        initAdminVendorBusinessAddressAutocomplete();
    }
});
</script>
@endif
