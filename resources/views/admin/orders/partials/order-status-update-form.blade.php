@php
    $formClass = $formClass ?? 'order-status-update-form';
    $selectClass = $selectClass ?? 'form-select form-select-sm';
@endphp
<form method="POST" action="{{ route('admin.update-order-status', $order->order_id) }}" class="{{ $formClass }}">
    @csrf
    @include('admin.orders.partials.order-status-quick-select', [
        'order' => $order,
        'selectClass' => $selectClass,
    ])
</form>
