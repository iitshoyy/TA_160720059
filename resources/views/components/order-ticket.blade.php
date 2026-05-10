@props([
    'order',
    'showActions' => false,
])

@php
    /**
     * Compact order/ticket card used by the Chef Kitchen Display.
     * Displays order #, table, customer, item list and elapsed time.
     */
    $elapsed = $order->order_date
        ? \Carbon\Carbon::parse($order->order_date)->diffForHumans(null, ['parts' => 1, 'short' => true])
        : '—';
@endphp

<article class="ticket ticket-{{ $order->status }}">
    <header class="ticket-head">
        <div>
            <div class="ticket-id">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</div>
            <div class="ticket-meta">
                <i class="fas fa-chair"></i> {{ $order->table->name ?? 'Takeaway' }}
                <span style="margin:0 6px; opacity:0.4;">•</span>
                <i class="far fa-clock"></i> {{ $elapsed }}
            </div>
        </div>
        <x-status-badge :status="$order->status" />
    </header>

    <ul class="ticket-items">
        @foreach($order->orderDetails as $detail)
            <li>
                <span class="ticket-qty">{{ $detail->quantity }}×</span>
                <span class="ticket-name">{{ $detail->menu->name ?? 'Item' }}</span>
            </li>
        @endforeach
    </ul>

    @if($order->notes)
        <div class="ticket-notes">
            <i class="fas fa-sticky-note"></i> {{ $order->notes }}
        </div>
    @endif

    @if($showActions)
        <footer class="ticket-actions">
            {{ $slot }}
        </footer>
    @endif
</article>
