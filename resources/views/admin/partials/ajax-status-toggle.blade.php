@php
    $checked = !empty($checked);
    $disabled = !empty($disabled);
    $wrapperClass = $wrapperClass ?? 'form-check form-switch m-0';
    $inputClass = trim('ajax-status-toggle ' . ($class ?? 'form-check-input'));
@endphp
<div class="{{ $wrapperClass }}">
    <input type="checkbox"
        class="{{ $inputClass }}"
        data-toggle-url="{{ $url }}"
        @if(!empty($statusPill)) data-status-pill="{{ $statusPill }}" @endif
        @if(!empty($statusLabel)) data-status-label="{{ $statusLabel }}" @endif
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        aria-label="{{ $ariaLabel ?? 'Toggle status' }}">
</div>
