@extends('layouts.app')
@section('title','Purchase Order Detail')
@section('page-title','Purchase Order Detail')

@section('content')
<div class="page-header">
    <div><h1>PO #{{ str_pad($po->id,4,'0',STR_PAD_LEFT) }}</h1><p>{{ $po->supplier->name }} — {{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</p></div>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
    <div class="card">
        <h3 style="color:var(--cream);margin-bottom:16px;">Order Items</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ingredient</th><th>Unit</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
                <tbody>
                    @foreach($po->items as $item)
                    <tr>
                        <td class="fw-500">{{ $item->ingridient->name ?? 'Unknown' }}</td>
                        <td>{{ $item->ingridient->unit ?? '-' }}</td>
                        <td>{{ number_format($item->quantity,2) }}</td>
                        <td>Rp {{ number_format($item->unit_price) }}</td>
                        <td class="text-gold fw-600">Rp {{ number_format($item->subtotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="text-align:right;margin-top:16px;font-size:1rem;font-weight:700;color:var(--cream);">Total: Rp {{ number_format($po->total_amount) }}</div>
    </div>
    <div class="card">
        <h3 style="color:var(--cream);margin-bottom:16px;">PO Info</h3>
        <div style="display:flex;flex-direction:column;gap:12px;font-size:.875rem;">
            <div><span class="text-muted">Status</span><br><span class="status status-{{ $po->status==='received'?'completed':'pending' }}">{{ ucfirst($po->status) }}</span></div>
            <div><span class="text-muted">Supplier</span><br><strong>{{ $po->supplier->name }}</strong></div>
            <div><span class="text-muted">Order Date</span><br>{{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</div>
            @if($po->expected_date)<div><span class="text-muted">Expected</span><br>{{ \Carbon\Carbon::parse($po->expected_date)->format('d M Y') }}</div>@endif
            @if($po->notes)<div><span class="text-muted">Notes</span><br>{{ $po->notes }}</div>@endif
        </div>
        @if(in_array($po->status,['pending','sent']))
        <div style="margin-top:20px;">
            <form method="POST" action="{{ route('purchase-orders.receive',$po->id) }}">@csrf @method('PATCH')
                <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;" onclick="return confirm('Mark as received? Stock will be updated.')"><i class="fas fa-check"></i> Mark as Received</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
