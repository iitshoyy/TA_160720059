@extends('layouts.app')
@section('title', 'Orders')
@section('page-title', 'Order Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Orders</h1>
        <p>Manage all restaurant orders and transactions</p>
    </div>
    <a href="{{ route('orders.create') }}" class="btn btn-gold"><i class="fas fa-plus"></i> New Order</a>
</div>

<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="margin:0; flex:1; min-width:180px;">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Order # or customer..." value="{{ request('search') }}">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                <option value="processing" {{ request('status')=='processing'?'selected':'' }}>Processing</option>
                <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Type</label>
            <select name="type" class="form-control">
                <option value="">All Types</option>
                <option value="dine-in" {{ request('type')=='dine-in'?'selected':'' }}>Dine-In</option>
                <option value="takeaway" {{ request('type')=='takeaway'?'selected':'' }}>Takeaway</option>
                <option value="pre-order" {{ request('type')=='pre-order'?'selected':'' }}>Pre-Order</option>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}">
        </div>
        <button type="submit" class="btn btn-outline"><i class="fas fa-filter"></i> Filter</button>
        <a href="{{ route('orders.index') }}" class="btn btn-outline"><i class="fas fa-times"></i> Clear</a>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Table / Type</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders ?? [] as $order)
                <tr>
                    <td class="text-gold fw-600">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $order->customer->name ?? $order->customer_name ?? 'Walk-in' }}</td>
                    <td>
                        <span style="font-size:0.78rem;">{{ $order->table->name ?? '' }}</span><br>
                        <span class="status status-{{ $order->order_type === 'dine-in' ? 'completed' : 'processing' }}" style="font-size:0.65rem;">
                            {{ ucfirst(str_replace('-',' ',$order->order_type)) }}
                        </span>
                    </td>
                    <td>{{ $order->orderDetails->count() ?? 0 }} items</td>
                    <td style="font-weight:600;">Rp {{ number_format($order->total_amount) }}</td>
                    <td>{{ $order->payment_date ? $order->payment_type : 'Unpaid' }}</td>
                    <td><span class="status status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                    <td style="font-size:0.78rem; color:var(--muted);">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m H:i') }}</td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            @if($order->status === 'pending' && $order->payment_date === null)
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-success btn-sm" title="Collect payment"><i class="fas fa-cash-register"></i></a>
                            @elseif($order->status === 'pending')
                            <form method="POST" action="{{ route('orders.update-status', $order->id) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="processing">
                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-fire"></i></button>
                            </form>
                            @endif
                            @if($order->status === 'processing')
                            <form method="POST" action="{{ route('orders.update-status', $order->id) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-gold btn-sm"><i class="fas fa-check"></i></button>
                            </form>
                            @endif
                            <a href="{{ route('orders.receipt', $order->id) }}" class="btn btn-outline btn-sm" target="_blank"><i class="fas fa-print"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state colspan="9" icon="fas fa-receipt" message="No orders found" />
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($orders) && $orders->hasPages())
    <div style="margin-top:16px;">{{ $orders->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
