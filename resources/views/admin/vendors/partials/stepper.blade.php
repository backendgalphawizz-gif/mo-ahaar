@php
    $steps = [
        'personal' => 'Personal Information',
        'business' => 'Business Information',
        'bank' => 'Bank Details',
        'documents' => 'Documents Information',
    ];
    $current = $tab ?? 'personal';
    $stepKeys = array_keys($steps);
    $currentIndex = array_search($current, $stepKeys, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
    $stepUrl = function (string $key) use ($isEdit, $vendor) {
        if ($isEdit && !empty($vendor)) {
            return route('admin.edit-vendor', ['id' => $vendor->vendor_id, 'tab' => $key]);
        }
        return route('admin.add-vendor', ['tab' => $key]);
    };
@endphp
<div class="figma-stepper">
    @foreach($steps as $key => $label)
        @php
            $index = array_search($key, $stepKeys, true);
            $stateClass = $key === $current ? 'active' : ($index !== false && $index < $currentIndex ? 'done' : '');
        @endphp
        <span class="figma-step {{ $stateClass }}">
            <span class="step-circle">{{ $loop->iteration }}</span>
            <span class="step-label">{{ $label }}</span>
        </span>
    @endforeach
</div>
