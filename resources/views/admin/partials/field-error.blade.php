@php
    $fieldName = $field ?? '';
@endphp
@if($fieldName !== '' && $errors->has($fieldName))
    <div class="text-danger small mt-1 fw-semibold">{{ $errors->first($fieldName) }}</div>
@endif
