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
    </div>
    <div class="stat-card success">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value">{{ $summary['orders'] ?? 0 }}</div>
        <div class="stat-sub">Completed transactions</div>
    </div>
    <div class="stat-card info">
        <div class="stat-label">Avg Order Value</div>
        <div class="stat-value" style="font-size:1.6rem;">Rp {{ number_format($summary['avg_order'] ?? 0) }}</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">Tax Collected</div>
        <div class="stat-value" style="font-size:1.6rem;">Rp {{ number_format($summary['tax'] ?? 0) }}</div>
        <div class="stat-sub">11% PPN</div>
    </div>
</div>

<!-- Profitability (HPP / COGS) -->
<div class="stat-grid" style="grid-template-columns: repeat(4,1fr); margin-bottom:24px;">
    <div class="stat-card warning">
        <div class="stat-label">HPP (COGS)</div>
        <div class="stat-value" style="font-size:1.6rem;">Rp {{ number_format($summary['cogs'] ?? 0) }}</div>
        <div class="stat-sub">Cost of goods sold</div>
    </div>
    <div class="stat-card info">
        <div class="stat-label">Net Sales</div>
        <div class="stat-value" style="font-size:1.6rem;">Rp {{ number_format($summary['net_sales'] ?? 0) }}</div>
        <div class="stat-sub">Revenue excl. tax</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Gross Profit</div>
        <div class="stat-value" style="font-size:1.6rem;">Rp {{ number_format($summary['gross_profit'] ?? 0) }}</div>
        <div class="stat-sub">Net sales − HPP</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Profit Margin</div>
        <div class="stat-value" style="font-size:1.6rem;">{{ number_format($summary['margin'] ?? 0, 1) }}%</div>
        <div class="stat-sub">Gross profit / net sales</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
    <!-- Best Selling Items -->
    <div class="card">
        <h3 style="color:var(--cream); margin-bottom:16px;">Best Selling Items</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Item</th><th>Qty Sold</th><th>Revenue</th><th>HPP</th><th>Profit</th></tr>
                </thead>
                <tbody>
                    @forelse($topItems ?? [] as $item)
                    <tr>
                        <td class="fw-500">{{ $item->menu_name }}</td>
                        <td>{{ $item->total_qty }}</td>
                        <td class="text-gold">Rp {{ number_format($item->total_revenue) }}</td>
                        <td>Rp {{ number_format($item->total_cost ?? 0) }}</td>
                        <td class="fw-500" style="color:{{ ($item->total_profit ?? 0) >= 0 ? 'var(--gold)' : '#e06666' }};">Rp {{ number_format($item->total_profit ?? 0) }}</td>
                    </tr>
                    @empty
                    <x-empty-state colspan="5" icon="fas fa-chart-line" message="No data" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment Method Breakdown -->
    <div class="card">
        <h3 style="color:var(--cream); margin-bottom:16px;">Payment Methods</h3>
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
                        <td class="text-gold">Rp {{ number_format($pm->total) }}</td>
                    </tr>
                    @empty
                    <x-empty-state colspan="3" icon="fas fa-chart-line" message="No data" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sales Trend Chart (simple bar visualization) -->
<div class="card" style="margin-bottom:20px;">
    <h3 style="color:var(--cream); margin-bottom:20px;">Sales Trend</h3>
    <div style="display:flex; align-items:flex-end; gap:6px; height:180px; padding-bottom:8px; border-bottom:1px solid var(--border);">
        @forelse($dailySales ?? [] as $day)
        @php $maxVal = collect($dailySales)->max('revenue') ?: 1; @endphp
        <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
            <div style="font-size:0.65rem; color:var(--gold);">{{ number_format($day->revenue/1000) }}K</div>
            <div style="
                width:100%;
                height:{{ max(4, ($day->revenue / $maxVal) * 140) }}px;
                background:var(--gold);
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
        <h3 class="text-cream">Transaction Details</h3>
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
                    <th>HPP</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions ?? [] as $order)
                @php
                    $tax = $order->total_amount * 0.11 / 1.11;
                    $sub = $order->total_amount - $tax;
                    $hpp = $order->orderDetails->sum(fn ($d) => ($menuCost[$d->menus_id] ?? 0) * (float) $d->quantity);
                @endphp
                <tr>
                    <td class="text-gold">#{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td style="font-size:0.78rem;">{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/y H:i') }}</td>
                    <td>{{ $order->customer_name ?? 'Walk-in' }}</td>
                    <td><span class="status status-completed" style="font-size:0.65rem;">{{ ucfirst(str_replace('-',' ',$order->order_type)) }}</span></td>
                    <td>{{ $order->orderDetails->count() }}</td>
                    <td>Rp {{ number_format($sub) }}</td>
                    <td>Rp {{ number_format($tax) }}</td>
                    <td class="fw-600 text-cream">Rp {{ number_format($order->total_amount) }}</td>
                    <td>Rp {{ number_format($hpp) }}</td>
                    <td>{{ ucfirst($order->payment_type ?? 'Cash') }}</td>
                </tr>
                @empty
                <x-empty-state colspan="10" icon="fas fa-receipt" message="No transactions in this period" />
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($transactions) && method_exists($transactions, 'hasPages') && $transactions->hasPages())
    <div style="margin-top:16px;">{{ $transactions->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
