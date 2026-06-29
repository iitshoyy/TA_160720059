@extends('layouts.app')
@section('title','Purchase Orders')
@section('page-title','Purchase Orders')

@section('content')
<div class="page-header">
    <div><h1>Purchase Orders</h1><p>Manage raw material purchasing from suppliers</p></div>
    <button class="btn btn-gold" onclick="openModal('addPOModal')"><i class="fas fa-plus"></i> New Purchase Order</button>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>PO #</th><th>Supplier</th><th>Order Date</th><th>Expected</th><th>Items</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                <tr>
                    <td class="text-gold fw-600">#{{ str_pad($po->id,4,'0',STR_PAD_LEFT) }}</td>
                    <td class="fw-500">{{ $po->supplier->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($po->order_date)->format('d/m/Y') }}</td>
                    <td class="text-muted">{{ $po->expected_date ? \Carbon\Carbon::parse($po->expected_date)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $po->items->count() }} items</td>
                    <td style="font-weight:600;">Rp {{ number_format($po->total_amount) }}</td>
                    <td><span class="status status-{{ $po->status==='received'?'completed':($po->status==='pending'?'pending':'processing') }}">{{ ucfirst($po->status) }}</span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="{{ route('purchase-orders.show',$po->id) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            @if($po->status==='pending' || $po->status==='sent')
                            <button type="button" class="btn btn-success btn-sm" onclick="openModal('receiveModal{{ $po->id }}')"><i class="fas fa-check"></i> Receive</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <x-empty-state colspan="8" icon="fas fa-file-invoice" message="No purchase orders yet" />
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px;">{{ $purchaseOrders->links() }}</div>
</div>

@foreach($purchaseOrders as $po)
    @if($po->status==='pending' || $po->status==='sent')
    <div class="modal-overlay" id="receiveModal{{ $po->id }}">
        <div class="modal" style="max-width:640px;">
            <div class="modal-header"><div class="modal-title">Receive PO #{{ str_pad($po->id,4,'0',STR_PAD_LEFT) }}</div><button class="modal-close" onclick="closeModal('receiveModal{{ $po->id }}')"><i class="fas fa-times"></i></button></div>
            <form method="POST" action="{{ route('purchase-orders.receive',$po->id) }}">@csrf @method('PATCH')
            <div class="modal-body">
                <p class="text-muted" style="margin-bottom:14px;font-size:.875rem;">Confirm the actual quantity received for each item. Inventory will be credited with these amounts.</p>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Ingredient</th><th>Ordered</th><th style="width:140px;">Received</th></tr></thead>
                        <tbody>
                            @foreach($po->items as $item)
                            <tr>
                                <td class="fw-500">{{ $item->ingridient->name ?? 'Unknown' }} <span class="text-muted">({{ $item->ingridient->unit ?? '-' }})</span></td>
                                <td>{{ number_format($item->quantity,2) }}</td>
                                <td><input type="number" name="received[{{ $item->id }}]" class="form-control" step="0.01" min="0" value="{{ rtrim(rtrim(number_format($item->quantity,2,'.',''),'0'),'.') }}" required></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('receiveModal{{ $po->id }}')">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Confirm Receipt</button>
            </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

<div class="modal-overlay" id="addPOModal">
    <div class="modal" style="max-width:700px;">
        <div class="modal-header"><div class="modal-title">New Purchase Order</div><button class="modal-close" onclick="closeModal('addPOModal')"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="{{ route('purchase-orders.store') }}">@csrf
        <div class="modal-body">
            <div class="form-row">
                <div class="form-group"><label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Expected Delivery</label><input type="date" name="expected_date" class="form-control"></div>
            </div>
            <div class="form-group"><label class="form-label">Notes</label><input name="notes" class="form-control" placeholder="Optional notes..."></div>

            <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:6px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <label class="form-label" style="margin:0;">Order Items</label>
                    <button type="button" class="btn btn-outline btn-sm" onclick="addPORow()"><i class="fas fa-plus"></i> Add Item</button>
                </div>
                <div id="poItems">
                    <div class="po-row" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:end;">
                        <div><label class="form-label">Ingredient</label>
                            <select name="items[0][ingredient_id]" class="form-control" required>
                                <option value="">Select...</option>
                                @foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->unit }})</option>@endforeach
                            </select>
                        </div>
                        <div><label class="form-label">Qty</label><input type="number" name="items[0][quantity]" class="form-control" step="0.01" required placeholder="0" oninput="calcTotal()"></div>
                        <div><label class="form-label">Unit Price</label><input type="number" name="items[0][unit_price]" class="form-control" required placeholder="0" oninput="calcTotal()"></div>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removePORow(this)" style="margin-bottom:0;"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <div style="text-align:right;margin-top:10px;font-size:.95rem;"><strong>Total: <span id="poTotal" class="text-gold">Rp 0</span></strong></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('addPOModal')">Cancel</button>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> Create PO</button>
        </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
let rowIdx = 1;
const ingredientOpts = `@foreach($ingredients as $i)<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->unit }})</option>@endforeach`;

function addPORow() {
    const div = document.createElement('div');
    div.className = 'po-row';
    div.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:end;';
    div.innerHTML = `
        <div><select name="items[${rowIdx}][ingredient_id]" class="form-control" required><option value="">Select...</option>${ingredientOpts}</select></div>
        <div><input type="number" name="items[${rowIdx}][quantity]" class="form-control" step="0.01" required placeholder="0" oninput="calcTotal()"></div>
        <div><input type="number" name="items[${rowIdx}][unit_price]" class="form-control" required placeholder="0" oninput="calcTotal()"></div>
        <button type="button" class="btn btn-danger btn-sm" onclick="removePORow(this)"><i class="fas fa-times"></i></button>`;
    document.getElementById('poItems').appendChild(div);
    rowIdx++;
}

function removePORow(btn) {
    btn.closest('.po-row').remove();
    calcTotal();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.po-row').forEach(row => {
        const q = parseFloat(row.querySelectorAll('input')[0]?.value)||0;
        const p = parseFloat(row.querySelectorAll('input')[1]?.value)||0;
        total += q * p;
    });
    document.getElementById('poTotal').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
}
</script>
@endpush
