@php
    $selectClass = $selectClass ?? 'form-select form-select-sm order-status-select status-pill-select';
    $adminStatuses = \App\Models\Orders::adminPrimaryFulfillmentStatuses();
    $current = (string) ($order->order_status ?? '');
    $isPrimary = array_key_exists($current, $adminStatuses);
@endphp
<select
    name="order_status"
    class="{{ $selectClass }}"
    data-order-id="{{ $order->order_id }}"
    data-order-number="{{ $order->order_number }}"
    data-current-status="{{ $order->order_status }}"
    aria-label="Change order status"
>
    @unless($isPrimary)
        <option value="{{ e($current) }}" selected>{{ \App\Models\Orders::statusLabel($current) }} (current)</option>
    @endunless
    @foreach($adminStatuses as $value => $label)
        <option value="{{ $value }}" @selected($isPrimary && $current === $value)>{{ $label }}</option>
    @endforeach
</select>
