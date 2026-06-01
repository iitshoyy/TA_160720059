@extends('layouts.app')
@section('title','Order Detail')
@section('page-title','Order Detail')

@section('content')
<div class="page-header">
    <div>
        <h1>Order #{{ str_pad($order->id,4,'0',STR_PAD_LEFT) }}</h1>
        <p>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, H:i') }}</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('orders.receipt',$order->id) }}" class="btn btn-outline" target="_blank"><i class="fas fa-print"></i> Print</a>
        <a href="{{ route('orders.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
    <div>
        <div class="card" style="margin-bottom:20px;">
            <h3 style="color:var(--cream);margin-bottom:16px;">Order Items</h3>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach($order->orderDetails as $d)
                        <tr>
                            <td class="fw-500">{{ $d->menu->name ?? 'Deleted item' }}</td>
                            <td>Rp {{ number_format($d->price) }}</td>
                            <td>{{ $d->quantity }}</td>
                            <td class="text-gold fw-600">Rp {{ number_format($d->subtotal) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @php $subtotal=$order->orderDetails->sum('subtotal'); $tax=$subtotal*0.11; @endphp
            <div style="margin-top:16px;border-top:1px solid var(--border);padding-top:14px;">
                <div style="display:flex;justify-content:space-between;color:var(--muted);font-size:.875rem;margin-bottom:6px;"><span>Subtotal</span><span>Rp {{ number_format($subtotal) }}</span></div>
                <div style="display:flex;justify-content:space-between;color:var(--muted);font-size:.875rem;margin-bottom:6px;"><span>Tax (11%)</span><span>Rp {{ number_format($tax) }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:700;color:var(--cream);"><span>Total</span><span>Rp {{ number_format($order->total_amount) }}</span></div>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <h3 style="color:var(--cream);margin-bottom:14px;">Order Info</h3>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:.875rem;">
                <div><span class="text-muted">Status</span><br><span class="status status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></div>
                <div><span class="text-muted">Type</span><br><span>{{ ucfirst(str_replace('-',' ',$order->order_type)) }}</span></div>
                <div><span class="text-muted">Customer</span><br><span>{{ $order->customer_name ?? 'Walk-in' }}</span></div>
                @if($order->table)<div><span class="text-muted">Table</span><br><span>{{ $order->table->name }}</span></div>@endif
                <div><span class="text-muted">Payment</span><br><span>{{ $order->payment_date ? ucfirst($order->payment_type) : 'Unpaid — collect at cashier' }}</span></div>
                @if($order->amount_paid)<div><span class="text-muted">Amount Paid</span><br><span>Rp {{ number_format($order->amount_paid) }}</span></div>@endif
                @if($order->notes)<div><span class="text-muted">Notes</span><br><span>{{ $order->notes }}</span></div>@endif
            </div>
        </div>

        @if($order->status === 'pending' && $order->payment_date === null)
        <div class="card">
            <h3 style="color:var(--cream);margin-bottom:14px;">Collect Payment</h3>
            <form method="POST" action="{{ route('orders.collect-payment',$order->id) }}" id="payForm">
                @csrf @method('PATCH')
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label">Method</label>
                    <select name="payment_type" class="form-control" required>
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="QRIS">QRIS</option>
                        <option value="Transfer">Transfer</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label class="form-label">Amount received (total: Rp {{ number_format($order->total_amount) }})</label>
                    <input type="number" name="amount_paid" class="form-control" min="{{ $order->total_amount }}" step="1" value="{{ old('amount_paid', $order->total_amount) }}" required oninput="document.getElementById('changeOut').textContent = 'Change: Rp ' + Math.max(0, this.value - {{ $order->total_amount }}).toLocaleString('id-ID')">
                </div>
                <div id="changeOut" style="color:var(--muted);font-size:.85rem;margin-bottom:12px;">Change: Rp 0</div>
                @error('amount_paid')<div style="color:#dc2626;font-size:.8rem;margin-bottom:10px;">{{ $message }}</div>@enderror
                <button class="btn btn-success" style="width:100%;justify-content:center;"><i class="fas fa-cash-register"></i> Confirm Payment → Processing</button>
            </form>
            <form method="POST" action="{{ route('orders.update-status',$order->id) }}" style="margin-top:8px;">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button class="btn btn-danger" style="width:100%;justify-content:center;"><i class="fas fa-times"></i> Cancel Order</button>
            </form>
        </div>
        @elseif(in_array($order->status,['pending','processing']))
        <div class="card">
            <h3 style="color:var(--cream);margin-bottom:14px;">Update Status</h3>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @if($order->status==='pending')
                <form method="POST" action="{{ route('orders.update-status',$order->id) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="processing">
                    <button class="btn btn-success" style="width:100%;justify-content:center;"><i class="fas fa-fire"></i> Mark as Processing</button>
                </form>
                @endif
                @if($order->status==='processing')
                <form method="POST" action="{{ route('orders.update-status',$order->id) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button class="btn btn-gold" style="width:100%;justify-content:center;"><i class="fas fa-check"></i> Mark as Completed</button>
                </form>
                @endif
                <form method="POST" action="{{ route('orders.update-status',$order->id) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button class="btn btn-danger" style="width:100%;justify-content:center;"><i class="fas fa-times"></i> Cancel Order</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
