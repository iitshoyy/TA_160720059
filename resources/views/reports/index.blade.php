@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Financial Reports')

@section('content')
<div class="page-header">
    <div>
        <h1>Reports</h1>
        <p>Daily, weekly, and monthly financial analytics</p>
    </div>
    <a href="{{ route('reports.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline">
        <i class="fas fa-download"></i> Export CSV
    </a>
</div>

<!-- Date Range Filter -->
<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <div class="form-group" style="margin:0;">
            <label class="form-label">Period</label>
            <select name="period" class="form-control" onchange="this.form.submit()">
                <option value="today" {{ request('period','today')=='today'?'selected':'' }}>Today</option>
                <option value="week" {{ request('period')=='week'?'selected':'' }}>This Week</option>
                <option value="month" {{ request('period')=='month'?'selected':'' }}>This Month</option>
                <option value="custom" {{ request('period')=='custom'?'selected':'' }}>Custom Range</option>
            </select>
        </div>
        @if(request('period') === 'custom')
        <div class="form-group" style="margin:0;">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        <button type="submit" class="btn btn-gold">Apply</button>
        @endif
    </form>
</div>

<!-- Summary Stats -->
<div class="stat-grid" style="grid-template-columns: repeat(4,1fr); margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value" style="font-size:1.6rem;">Rp {{ number_format($summary['revenue'] ?? 0) }}</div>
        <div class="stat-sub">Gross income</div>
        <i class="fas fa-money-bill stat-icon"></i>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value">{{ $summary['orders'] ?? 0 }}</div>
        <div class="stat-sub">Completed transactions</div>
        <i class="fas fa-receipt stat-icon"></i>
    </div>
    <div class="stat-card info">
        <div class="stat-label">Avg Order Value</div>
        <div class="stat-value" style="font-size:1.6rem;">Rp {{ number_format($summary['avg_order'] ?? 0) }}</div>
        <i class="fas fa-chart-line stat-icon"></i>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">Tax Collected</div>
        <div class="stat-value" style="font-size:1.6rem;">Rp {{ number_format($summary['tax'] ?? 0) }}</div>
        <div class="stat-sub">11% PPN</div>
        <i class="fas fa-percent stat-icon"></i>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
    <!-- Best Selling Items -->
    <div class="card">
        <h3 style="font-family:'Playfair Display',serif; color:var(--cream); margin-bottom:16px;">Best Selling Items</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Item</th><th>Qty Sold</th><th>Revenue</th></tr>
                </thead>
                <tbody>
                    @forelse($topItems ?? [] as $item)
                    <tr>
                        <td style="font-weight:500;">{{ $item->menu_name }}</td>
                        <td>{{ $item->total_qty }}</td>
                        <td style="color:var(--gold);">Rp {{ number_format($item->total_revenue) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="color:var(--muted); text-align:center; padding:20px;">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Method Breakdown -->
    <div class="card">
        <h3 style="font-family:'Playfair Display',serif; color:var(--cream); margin-bottom:16px;">Payment Methods</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Method</th><th>Count</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @forelse($paymentBreakdown ?? [] as $pm)
                    <tr>
                        <td><i class="fas fa-{{ $pm->payment_type === 'cash' ? 'money-bill' : ($pm->payment_type === 'card' ? 'credit-card' : 'qrcode') }}" style="color:var(--gold); margin-right:6px;"></i>{{ ucfirst($pm->payment_type) }}</td>
                        <td>{{ $pm->count }}</td>
                        <td style="color:var(--gold);">Rp {{ number_format($pm->total) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="color:var(--muted); text-align:center; padding:20px;">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sales Trend Chart (simple bar visualization) -->
<div class="card" style="margin-bottom:20px;">
    <h3 style="font-family:'Playfair Display',serif; color:var(--cream); margin-bottom:20px;">Sales Trend</h3>
    <div style="display:flex; align-items:flex-end; gap:6px; height:180px; padding-bottom:8px; border-bottom:1px solid var(--border);">
        @forelse($dailySales ?? [] as $day)
        @php $maxVal = collect($dailySales)->max('revenue') ?: 1; @endphp
        <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
            <div style="font-size:0.65rem; color:var(--gold);">{{ number_format($day->revenue/1000) }}K</div>
            <div style="
                width:100%;
                height:{{ max(4, ($day->revenue / $maxVal) * 140) }}px;
                background:linear-gradient(to top, var(--gold), var(--gold-light));
                border-radius:4px 4px 0 0;
                transition: all 0.3s;
            " title="Rp {{ number_format($day->revenue) }}"></div>
            <div style="font-size:0.65rem; color:var(--muted);">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</div>
        </div>
        @empty
        <div style="color:var(--muted); text-align:center; width:100%; padding:40px;">No sales data for this period</div>
        @endforelse
    </div>
</div>

<!-- Detailed Transaction Table -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h3 style="font-family:'Playfair Display',serif; color:var(--cream);">Transaction Details</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Items</th>
                    <th>Subtotal</th>
                    <th>Tax</th>
                    <th>Total</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions ?? [] as $order)
                @php
                    $tax = $order->total_amount * 0.11 / 1.11;
                    $sub = $order->total_amount - $tax;
                @endphp
                <tr>
                    <td style="color:var(--gold);">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td style="font-size:0.78rem;">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/y H:i') }}</td>
                    <td>{{ $order->customer_name ?? 'Walk-in' }}</td>
                    <td><span class="status status-completed" style="font-size:0.65rem;">{{ ucfirst(str_replace('-',' ',$order->order_type)) }}</span></td>
                    <td>{{ $order->orderDetails->count() }}</td>
                    <td>Rp {{ number_format($sub) }}</td>
                    <td>Rp {{ number_format($tax) }}</td>
                    <td style="font-weight:600; color:var(--cream);">Rp {{ number_format($order->total_amount) }}</td>
                    <td>{{ ucfirst($order->payment_type ?? 'Cash') }}</td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center; color:var(--muted); padding:30px;">No transactions in this period</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($transactions) && method_exists($transactions, 'hasPages') && $transactions->hasPages())
    <div style="margin-top:16px;">{{ $transactions->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
