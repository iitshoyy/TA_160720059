@extends('layouts.app')
@section('title', 'Cashier Console')
@section('page-title', 'Cashier Console')

@section('content')
<x-page-header
    title="Hi {{ Auth::user()->name }}"
    subtitle="Keep things moving — open transactions and table availability at a glance.">
    <a href="{{ route('orders.create') }}" class="btn btn-gold">
        <i class="fas fa-cash-register"></i> New Transaction
    </a>
    <a href="{{ route('orders.index') }}" class="btn btn-outline">
        <i class="fas fa-list"></i> All Orders
    </a>
</x-page-header>

<div class="stat-grid">
    <x-stat-card
        label="My Transactions Today"
        value="{{ $myTodayCount }}"
        sub="Orders processed by you" />
    <x-stat-card
        tone="success"
        label="My Sales Today"
        value="Rp {{ number_format($myTodayTotal) }}"
        sub="Completed and paid" />
    <x-stat-card
        tone="warning"
        label="Awaiting Payment"
        value="{{ $awaitingPay }}"
        sub="Open tabs to close" />
    <x-stat-card
        tone="info"
        label="Tables Available"
        value="{{ $availTables }}"
        sub="Ready to seat new guests" />
</div>

<div style="display:grid; grid-template-columns: 1.4fr 1fr; gap:20px;">
    <x-section-card title="Open Orders — Need Action">
        <x-slot:action>
            <a href="{{ route('orders.index') }}?status=pending" class="btn btn-outline btn-sm">All Pending</a>
        </x-slot:action>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Order #</th><th>Table / Customer</th><th>Total</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                @forelse($openOrders as $order)
                    <tr>
                        <td>#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <div class="text-cream">{{ $order->table->name ?? 'Takeaway' }}</div>
                            <div style="font-size:0.75rem; color:var(--muted);">{{ $order->customer_name }}</div>
                        </td>
                        <td>Rp {{ number_format($order->total_amount) }}</td>
                        <td><x-status-badge :status="$order->status" /></td>
                        <td>
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <x-empty-state colspan="5" icon="fas fa-check-circle" message="All clear — no open orders" />
                @endforelse
                </tbody>
            </table>
        </div>
    </x-section-card>

    <x-section-card title="My Recent Transactions">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Order #</th><th>Table</th><th>Total</th></tr>
                </thead>
                <tbody>
                @forelse($recentTransactions as $tx)
                    <tr>
                        <td>#{{ str_pad($tx->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $tx->table->name ?? '—' }}</td>
                        <td>Rp {{ number_format($tx->total_amount) }}</td>
                    </tr>
                @empty
                    <x-empty-state colspan="3" icon="fas fa-receipt" message="No transactions yet today" />
                @endforelse
                </tbody>
            </table>
        </div>
    </x-section-card>
</div>
@endsection
