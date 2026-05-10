<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ str_pad($order->id,4,'0',STR_PAD_LEFT) }}</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Courier New',monospace;background:#fff;color:#000;padding:20px;max-width:320px;margin:0 auto}
        .center{text-align:center}
        .logo{font-size:1.3rem;font-weight:bold;margin-bottom:4px}
        .divider{border-top:1px dashed #000;margin:10px 0}
        .row{display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:3px}
        .total-row{display:flex;justify-content:space-between;font-weight:bold;font-size:1rem;margin-top:4px;padding-top:4px;border-top:1px solid #000}
        .small{font-size:0.75rem;color:#555}
        .status-badge{display:inline-block;padding:3px 10px;border:1px solid #000;border-radius:3px;font-size:0.75rem;font-weight:bold;margin-top:4px}
        .btn-print{display:block;width:100%;margin-top:20px;padding:10px;background:#000;color:#fff;border:none;cursor:pointer;font-size:0.9rem;border-radius:4px}
        .btn-back{display:inline-block;margin-top:8px;color:#666;font-size:0.8rem;text-decoration:none}
        @media print{.no-print{display:none}body{padding:0}}
    </style>
</head>
<body>
<div class="center">
    <div class="logo">🍽️ RestoPOS</div>
    <div class="small">Restaurant Management System</div>
</div>
<div class="divider"></div>
<div class="row"><span>Order #</span><span>{{ str_pad($order->id,4,'0',STR_PAD_LEFT) }}</span></div>
<div class="row"><span>Date</span><span>{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') }}</span></div>
<div class="row"><span>Type</span><span>{{ ucfirst(str_replace('-',' ',$order->order_type)) }}</span></div>
@if($order->table)<div class="row"><span>Table</span><span>{{ $order->table->name }}</span></div>@endif
<div class="row"><span>Customer</span><span>{{ $order->customer_name ?? 'Walk-in' }}</span></div>
<div class="divider"></div>

@foreach($order->orderDetails as $detail)
<div class="row">
    <span>{{ $detail->menu->name ?? 'Item' }} x{{ $detail->quantity }}</span>
    <span>Rp {{ number_format($detail->subtotal) }}</span>
</div>
@endforeach

<div class="divider"></div>
@php
    $subtotal = $order->orderDetails->sum('subtotal');
    $tax      = $subtotal * 0.11;
    $total    = $subtotal + $tax;
@endphp
<div class="row"><span>Subtotal</span><span>Rp {{ number_format($subtotal) }}</span></div>
<div class="row"><span>Tax (11%)</span><span>Rp {{ number_format($tax) }}</span></div>
<div class="total-row"><span>TOTAL</span><span>Rp {{ number_format($total) }}</span></div>
@if($order->amount_paid)
<div class="row" style="margin-top:4px"><span>Paid</span><span>Rp {{ number_format($order->amount_paid) }}</span></div>
<div class="row"><span>Change</span><span>Rp {{ number_format(max(0,$order->amount_paid - $total)) }}</span></div>
@endif
<div class="divider"></div>
<div class="row"><span>Payment</span><span>{{ ucfirst($order->payment_type ?? 'Cash') }}</span></div>
<div class="row"><span>Status</span><span class="status-badge">{{ strtoupper($order->status) }}</span></div>
<div class="divider"></div>
<div class="center small">Thank you for dining with us!<br>Please come again 😊</div>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Print Receipt</button>
    <div class="center"><a class="btn-back" href="{{ route('orders.index') }}">← Back to Orders</a></div>
    <div class="center" style="margin-top:8px"><a class="btn-back" href="{{ route('orders.create') }}" style="color:#000;font-weight:bold">+ New Order</a></div>
</div>
</body>
</html>
