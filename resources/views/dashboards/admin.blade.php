@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Executive Overview')

@section('content')
<x-page-header
    title="Welcome back, {{ Auth::user()->name }}"
    subtitle="Here's the operational picture for {{ now()->format('l, d M Y') }}." />

<div class="stat-grid">
    <x-stat-card
        label="Today's Revenue"
        value="Rp {{ number_format($todayRevenue ?? 0) }}"
        sub="{{ $todayOrders ?? 0 }} transactions" />
    <x-stat-card
        tone="success"
        label="7-Day Revenue"
        value="Rp {{ number_format($weekRevenue ?? 0) }}"
        sub="Rolling weekly total" />
    <x-stat-card
        tone="info"
        label="Reservations Today"
        value="{{ $todayReservations ?? 0 }}"
        sub="Bookings on the floor" />
    <x-stat-card
        tone="warning"
        label="Pending Orders"
        value="{{ $pendingOrders ?? 0 }}"
        sub="Awaiting kitchen" />
    <x-stat-card
        tone="danger"
        label="Low Stock Items"
        value="{{ $lowStock ?? 0 }}"
        sub="Below reorder point" />
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
    <x-section-card title="Recent Orders">
        <x-slot:action>
            <a href="{{ route('orders.index') }}" class="btn btn-outline btn-sm">View All</a>
        </x-slot:action>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Order #</th><th>Table</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td>#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $order->table->name ?? 'Takeaway' }}</td>
                        <td>Rp {{ number_format($order->total_amount) }}</td>
                        <td><x-status-badge :status="$order->status" /></td>
                    </tr>
                @empty
                    <x-empty-state colspan="4" icon="fas fa-receipt" message="No orders yet today" />
                @endforelse
                </tbody>
            </table>
        </div>
    </x-section-card>

    <x-section-card title="Today's Reservations">
        <x-slot:action>
            <a href="{{ route('reservations.index') }}" class="btn btn-outline btn-sm">Manage</a>
        </x-slot:action>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Guest</th><th>Time</th><th>Pax</th><th>Status</th></tr>
                </thead>
                <tbody>
                @forelse($reservations as $res)
                    <tr>
                        <td>{{ $res->customer_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($res->reservation_time)->format('H:i') }}</td>
                        <td>{{ $res->guests }}</td>
                        <td><x-status-badge :status="$res->status" /></td>
                    </tr>
                @empty
                    <x-empty-state colspan="4" icon="fas fa-calendar" message="No reservations today" />
                @endforelse
                </tbody>
            </table>
        </div>
    </x-section-card>
</div>

<x-section-card title="Table Status Overview">
    <x-slot:action>
        <a href="{{ route('tables.index') }}" class="btn btn-outline btn-sm">Manage Tables</a>
    </x-slot:action>
    <div class="table-map">
        @forelse($tables as $table)
            <a href="{{ route('tables.index') }}" class="table-tile t-{{ $table->status }}">
                <div class="table-tile-icon">🪑</div>
                <div class="table-tile-name">{{ $table->name }}</div>
                <div class="table-tile-cap">{{ $table->capacity }} pax</div>
                <x-status-badge :status="$table->status" class="" style="margin-top:6px; display:inline-block;" />
            </a>
        @empty
            <x-empty-state icon="fas fa-chair" message="No tables configured" />
        @endforelse
    </div>
</x-section-card>
@endsection
