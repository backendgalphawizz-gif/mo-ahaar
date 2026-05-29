@php
    $steps = [
        'personal' => 'Personal Information',
        'vehicle' => 'Vehicle Information',
        'bank' => 'Bank Details',
    ];
    $current = $tab ?? 'personal';
    $stepKeys = array_keys($steps);
    $currentIndex = array_search($current, $stepKeys, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
        $current = 'personal';
    }
@endphp
<div class="figma-stepper" id="driverStepper">
    @foreach($steps as $key => $label)
        @php
            $index = array_search($key, $stepKeys, true);
            $stateClass = $key === $current ? 'active' : ($index !== false && $index < $currentIndex ? 'done' : '');
        @endphp
        <span class="figma-step {{ $stateClass }}" data-step="{{ $key }}">
            <span class="step-circle">{{ $loop->iteration }}</span>
            <span class="step-label">{{ $label }}</span>
        </span>
    @endforeach
</div>
