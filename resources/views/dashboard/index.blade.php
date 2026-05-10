@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Today's Revenue</div>
        <div class="stat-value">Rp {{ number_format($todayRevenue ?? 0) }}</div>
        <div class="stat-sub">{{ $todayOrders ?? 0 }} transactions today</div>
        <i class="fas fa-coins stat-icon"></i>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Orders Today</div>
        <div class="stat-value">{{ $todayOrders ?? 0 }}</div>
        <div class="stat-sub">{{ $pendingOrders ?? 0 }} pending</div>
        <i class="fas fa-receipt stat-icon"></i>
    </div>
    <div class="stat-card info">
        <div class="stat-label">Reservations</div>
        <div class="stat-value">{{ $todayReservations ?? 0 }}</div>
        <div class="stat-sub">Today's bookings</div>
        <i class="fas fa-calendar stat-icon"></i>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">Low Stock Items</div>
        <div class="stat-value">{{ $lowStock ?? 0 }}</div>
        <div class="stat-sub">Need restocking</div>
        <i class="fas fa-exclamation-triangle stat-icon"></i>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
    <!-- Recent Orders -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="font-family:'Playfair Display',serif; color:var(--cream); font-size:1rem;">Recent Orders</h3>
            <a href="{{ route('orders.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Table</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders ?? [] as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->table->name ?? 'Takeaway' }}</td>
                        <td>Rp {{ number_format($order->total_amount) }}</td>
                        <td><span class="status status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:24px;">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Today's Reservations -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <h3 style="font-family:'Playfair Display',serif; color:var(--cream); font-size:1rem;">Today's Reservations</h3>
            <a href="{{ route('reservations.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Time</th>
                        <th>Pax</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations ?? [] as $res)
                    <tr>
                        <td>{{ $res->customer_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($res->reservation_time)->format('H:i') }}</td>
                        <td>{{ $res->guests }}</td>
                        <td><span class="status status-{{ $res->status }}">{{ ucfirst($res->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:24px;">No reservations today</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Table Status Overview -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <h3 style="font-family:'Playfair Display',serif; color:var(--cream); font-size:1rem;">Table Status Overview</h3>
        <a href="{{ route('tables.index') }}" class="btn btn-outline btn-sm">Manage Tables</a>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(90px,1fr)); gap:12px;">
        @forelse($tables ?? [] as $table)
        <div style="
            background: var(--surface2);
            border: 1px solid {{ $table->status === 'available' ? '#27ae60' : ($table->status === 'occupied' ? '#c0392b' : '#2980b9') }};
            border-radius: 10px;
            padding: 14px;
            text-align: center;
        ">
            <div style="font-size:1.4rem;">🪑</div>
            <div style="font-size:0.78rem; font-weight:600; color:var(--cream); margin-top:4px;">{{ $table->name }}</div>
            <div style="font-size:0.65rem; color:var(--muted);">{{ $table->capacity }} pax</div>
            <span class="status status-{{ $table->status }}" style="margin-top:6px; display:block;">{{ ucfirst($table->status) }}</span>
        </div>
        @empty
        <div style="color:var(--muted); font-size:0.85rem; padding:12px;">No tables configured</div>
        @endforelse
    </div>
</div>
@endsection
