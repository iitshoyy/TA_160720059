@extends('layouts.app')
@section('title', 'Kitchen Display')
@section('page-title', 'Kitchen Display System')

@push('styles')
<style>
    /* Kitchen-specific layout: two columns of live tickets */
    .kds-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .kds-col-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--border);
    }
    .kds-col-head h3 {
        font-family: 'Playfair Display', serif; color: var(--cream);
        font-size: 1.05rem; display: flex; align-items: center; gap: 10px;
    }
    .kds-counter {
        background: var(--surface2); border: 1px solid var(--border);
        padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; color: var(--muted);
    }
    @media (max-width: 900px) { .kds-columns { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<x-page-header
    title="Kitchen Display"
    subtitle="Live order queue. Move tickets through Pending → Preparing → Done." />

<div class="stat-grid">
    <x-stat-card
        tone="warning"
        label="Pending Tickets"
        value="{{ $pendingCount }}"
        sub="Waiting to start"
        icon="fas fa-hourglass-start" />
    <x-stat-card
        tone="info"
        label="Now Preparing"
        value="{{ $processingCount }}"
        sub="On the line"
        icon="fas fa-fire-burner" />
    <x-stat-card
        tone="success"
        label="Completed Today"
        value="{{ $completedToday }}"
        sub="Sent out"
        icon="fas fa-check-double" />
    <x-stat-card
        tone="danger"
        label="Low-Stock Ingredients"
        value="{{ $lowStock->count() }}"
        sub="Check before service"
        icon="fas fa-triangle-exclamation" />
</div>

<div class="kds-columns">
    <div>
        <div class="kds-col-head">
            <h3><i class="fas fa-hourglass-start" style="color:var(--warning);"></i> Pending</h3>
            <span class="kds-counter">{{ $pendingTickets->count() }} ticket(s)</span>
        </div>
        <div class="ticket-grid">
            @forelse($pendingTickets as $order)
                <x-order-ticket :order="$order" :showActions="true">
                    <form method="POST" action="{{ route('orders.update-status', $order->id) }}" style="width:100%;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="processing">
                        <button class="btn btn-gold btn-sm" style="width:100%;">
                            <i class="fas fa-play"></i> Start Preparing
                        </button>
                    </form>
                </x-order-ticket>
            @empty
                <x-empty-state icon="fas fa-mug-hot" message="No pending tickets — kitchen is clear" />
            @endforelse
        </div>
    </div>

    <div>
        <div class="kds-col-head">
            <h3><i class="fas fa-fire-burner" style="color:var(--info);"></i> Preparing</h3>
            <span class="kds-counter">{{ $processingTickets->count() }} ticket(s)</span>
        </div>
        <div class="ticket-grid">
            @forelse($processingTickets as $order)
                <x-order-ticket :order="$order" :showActions="true">
                    <form method="POST" action="{{ route('orders.update-status', $order->id) }}" style="width:100%;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button class="btn btn-success btn-sm" style="width:100%;">
                            <i class="fas fa-check"></i> Mark Ready
                        </button>
                    </form>
                </x-order-ticket>
            @empty
                <x-empty-state icon="fas fa-utensils" message="Nothing on the line right now" />
            @endforelse
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;">
    <x-section-card title="Low-Stock Alerts">
        <x-slot:action>
            <a href="{{ route('inventory.index') }}" class="btn btn-outline btn-sm">Inventory</a>
        </x-slot:action>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ingredient</th><th>On Hand</th><th>Min</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($lowStock as $ing)
                    @php $qty = $ing->inventories->first()->quantity_on_hand ?? 0; @endphp
                    <tr>
                        <td>{{ $ing->name }}</td>
                        <td>{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }} {{ $ing->unit }}</td>
                        <td>{{ $ing->min_stock }} {{ $ing->unit }}</td>
                        <td><x-status-badge status="low" label="Low" /></td>
                    </tr>
                @empty
                    <x-empty-state colspan="4" icon="fas fa-box-open" message="All ingredients above reorder point" />
                @endforelse
                </tbody>
            </table>
        </div>
    </x-section-card>

    <x-section-card title="Unavailable Menu Items">
        <x-slot:action>
            <a href="{{ route('menus.index') }}" class="btn btn-outline btn-sm">Manage Menu</a>
        </x-slot:action>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Menu</th><th>Category</th><th>Price</th></tr></thead>
                <tbody>
                @forelse($unavailableMenus as $menu)
                    <tr>
                        <td>{{ $menu->name }}</td>
                        <td>{{ $menu->category->name ?? '—' }}</td>
                        <td>Rp {{ number_format($menu->price) }}</td>
                    </tr>
                @empty
                    <x-empty-state colspan="3" icon="fas fa-circle-check" message="All menu items are available" />
                @endforelse
                </tbody>
            </table>
        </div>
    </x-section-card>
</div>
@endsection
