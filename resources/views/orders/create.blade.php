@extends('layouts.app')
@section('title', 'New Order')
@section('page-title', 'New Transaction')

@push('styles')
<style>
.pos-grid { display: grid; grid-template-columns: 1fr 400px; gap: 16px; height: calc(100vh - 110px); }
.menu-panel { display: flex; flex-direction: column; overflow: hidden; background: var(--surface); border: 1px solid var(--border); }
.order-panel { display: flex; flex-direction: column; background: var(--surface); border: 1px solid var(--border); overflow: hidden; }
.search-bar { padding: 10px 12px; border-bottom: 1px solid var(--border); }
.search-bar .form-control { padding: 7px 10px; }
.category-tabs { display: flex; gap: 6px; padding: 8px 12px; overflow-x: auto; border-bottom: 1px solid var(--border); }
.cat-btn { padding: 5px 12px; border: 1px solid var(--border); background: transparent; color: var(--muted); font-size: 0.8rem; cursor: pointer; white-space: nowrap; font-family: inherit; }
.cat-btn.active, .cat-btn:hover { background: var(--gold); color: #000; border-color: var(--gold); }
.menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); gap: 8px; padding: 12px; overflow-y: auto; flex: 1; align-content: start; }
.menu-item {
    background: var(--surface2);
    border: 1px solid var(--border);
    padding: 10px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    text-align: left;
    min-height: 0;
}
.menu-item:hover { border-color: var(--gold); }
.menu-item.unavailable { opacity: 0.4; cursor: not-allowed; }
.menu-item .item-emoji { font-size: 1.4rem; flex-shrink: 0; }
.menu-item .item-body { flex: 1; min-width: 0; }
.menu-item .item-name { font-size: 0.85rem; font-weight: 600; color: var(--cream); line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.menu-item .item-price { font-size: 0.78rem; color: var(--gold); font-weight: 600; margin-top: 2px; }

.order-section { padding: 12px 14px; border-bottom: 1px solid var(--border); }
.order-section-label { font-size: 0.75rem; color: var(--muted); margin-bottom: 6px; }
.order-header h3 { font-size: 0.95rem; color: var(--cream); font-weight: 600; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
.order-type-btns { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 4px; }
.type-btn { padding: 6px 4px; border: 1px solid var(--border); background: transparent; color: var(--muted); font-size: 0.78rem; cursor: pointer; font-family: inherit; }
.type-btn.active { background: var(--gold); color: #000; border-color: var(--gold); font-weight: 600; }

.order-items { flex: 1; overflow-y: auto; padding: 10px 14px; min-height: 80px; }
.order-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--border); }
.order-item:last-child { border-bottom: none; }
.order-item .item-info { flex: 1; min-width: 0; }
.order-item .item-info .name { font-size: 0.85rem; font-weight: 500; color: var(--cream); }
.order-item .item-info .price { font-size: 0.75rem; color: var(--muted); margin-top: 2px; }
.qty-ctrl { display: flex; align-items: center; gap: 6px; }
.qty-btn { width: 22px; height: 22px; border: 1px solid var(--border); background: var(--surface); color: var(--text); cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; font-family: inherit; }
.qty-btn:hover { border-color: var(--gold); color: var(--gold); }
.qty-num { font-weight: 600; font-size: 0.85rem; min-width: 20px; text-align: center; }

.order-footer { padding: 12px 14px; border-top: 1px solid var(--border); background: var(--surface2); }
.totals-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--muted); margin-bottom: 4px; }
.totals-row.grand { color: var(--cream); font-weight: 700; font-size: 1rem; padding-top: 6px; border-top: 1px solid var(--border); margin-top: 6px; }

.table-select-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px; max-height: 160px; overflow-y: auto; }
.table-opt { padding: 6px 4px; border: 1px solid var(--border); text-align: center; cursor: pointer; font-size: 0.75rem; }
.table-opt:hover:not(.occupied) { border-color: var(--gold); color: var(--gold); }
.table-opt.selected { background: var(--gold); color: #000; border-color: var(--gold); font-weight: 600; }
.table-opt.occupied { opacity: 0.3; cursor: not-allowed; }

@media (max-width: 1100px) {
    .pos-grid { grid-template-columns: 1fr 360px; }
}
@media (max-width: 900px) {
    .pos-grid { grid-template-columns: 1fr; height: auto; }
    .order-panel { position: sticky; bottom: 0; }
}
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('orders.store') }}" id="orderForm">
@csrf
<input type="hidden" name="order_type" id="orderTypeInput" value="dine-in">
<input type="hidden" name="table_id" id="tableIdInput">
<input type="hidden" name="items" id="itemsInput">

<div class="pos-grid">
    <!-- LEFT: Menu Panel -->
    <div class="menu-panel">
        <div class="search-bar">
            <input type="text" id="menuSearch" class="form-control" placeholder="Search menu items..." oninput="filterMenu()">
        </div>
        <div class="category-tabs">
            <button type="button" class="cat-btn active" onclick="filterCategory('all', this)">All</button>
            @foreach($categories ?? [] as $cat)
            <button type="button" class="cat-btn" onclick="filterCategory('{{ $cat->id }}', this)">{{ $cat->name }}</button>
            @endforeach
        </div>
        <div class="menu-grid" id="menuGrid">
            @foreach($menus ?? [] as $menu)
            <div class="menu-item {{ $menu->availability ? '' : 'unavailable' }}"
                 data-id="{{ $menu->id }}"
                 data-name="{{ $menu->name }}"
                 data-price="{{ $menu->price }}"
                 data-category="{{ $menu->categoryMenus_id }}"
                 onclick="{{ $menu->availability ? 'addItem(this)' : '' }}">
                <div class="item-emoji">🍽️</div>
                <div class="item-body">
                    <div class="item-name">{{ $menu->name }}</div>
                    <div class="item-price">Rp {{ number_format($menu->price) }}@if(!$menu->availability) <span style="color:var(--danger); margin-left:6px;">· Unavailable</span>@endif</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- RIGHT: Order Panel -->
    <div class="order-panel">
        <div class="order-section order-header">
            <h3><i class="fas fa-receipt" class="text-gold"></i> Current Order</h3>
            <div class="order-type-btns">
                <button type="button" class="type-btn active" onclick="setOrderType('dine-in', this)">Dine-In</button>
                <button type="button" class="type-btn" onclick="setOrderType('takeaway', this)">Takeaway</button>
                <button type="button" class="type-btn" onclick="setOrderType('pre-order', this)">Pre-Order</button>
            </div>
        </div>

        <!-- Table Selection (for dine-in) -->
        <div id="tableSection" class="order-section">
            <div class="order-section-label">Select Table</div>
            <div class="table-select-grid">
                @foreach($tables ?? [] as $table)
                <div class="table-opt {{ $table->status === 'occupied' ? 'occupied' : '' }}"
                     data-table="{{ $table->id }}"
                     onclick="{{ $table->status !== 'occupied' ? 'selectTable(this)' : '' }}">
                    <div>{{ $table->name }}</div>
                    <div style="font-size:0.65rem; color:var(--muted);">{{ $table->capacity }}p</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Customer Name -->
        <div class="order-section">
            <div class="order-section-label">Customer (optional)</div>
            <input type="text" name="customer_name" class="form-control" placeholder="Customer name">
        </div>

        <!-- Order Items -->
        <div class="order-items" id="orderItems">
            <div id="emptyCart" style="text-align:center; color:var(--muted); padding:28px 16px; font-size:0.85rem;">
                Tap menu items to add to order
            </div>
        </div>

        <!-- Totals & Checkout -->
        <div class="order-footer">
            <div class="totals-row"><span>Subtotal</span><span id="subtotalDisplay">Rp 0</span></div>
            <div class="totals-row"><span>Tax (11%)</span><span id="taxDisplay">Rp 0</span></div>
            <div class="totals-row grand"><span>TOTAL</span><span id="totalDisplay">Rp 0</span></div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; margin:12px 0 8px;">
                <select name="payment_type" class="form-control">
                    <option value="Cash">Cash</option>
                    <option value="Transfer">Transfer</option>
                    <option value="QRIS">QRIS</option>
                    <option value="Card">Card</option>
                </select>
                <input type="number" name="amount_paid" id="amountPaid" class="form-control" placeholder="Amount paid" oninput="calcChange()">
            </div>
            <div id="changeDisplay" style="font-size:0.8rem; color:var(--gold); margin-bottom:8px; min-height:1em;"></div>
            <button type="submit" class="btn btn-gold" style="width:100%; justify-content:center;" onclick="return prepareSubmit()">
                <i class="fas fa-check-circle"></i> Place Order
            </button>
            <button type="button" class="btn btn-outline" style="width:100%; justify-content:center; margin-top:6px;" onclick="clearCart()">
                <i class="fas fa-trash"></i> Clear
            </button>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
let cart = {};
let orderTotal = 0;

function addItem(el) {
    const id = el.dataset.id;
    const name = el.dataset.name;
    const price = parseFloat(el.dataset.price);
    if (cart[id]) {
        cart[id].qty++;
    } else {
        cart[id] = { id, name, price, qty: 1 };
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('orderItems');
    const empty = document.getElementById('emptyCart');
    if (Object.keys(cart).length === 0) {
        container.innerHTML = '';
        container.appendChild(empty);
        empty.style.display = 'block';
        updateTotals(0);
        return;
    }
    let html = '';
    let subtotal = 0;
    for (const id in cart) {
        const item = cart[id];
        const itemTotal = item.price * item.qty;
        subtotal += itemTotal;
        html += `<div class="order-item">
            <div class="item-info">
                <div class="name">${item.name}</div>
                <div class="price">Rp ${formatNum(item.price)} × ${item.qty} = <strong style="color:var(--cream)">Rp ${formatNum(itemTotal)}</strong></div>
            </div>
            <div class="qty-ctrl">
                <button type="button" class="qty-btn" onclick="changeQty('${id}', -1)">−</button>
                <span class="qty-num">${item.qty}</span>
                <button type="button" class="qty-btn" onclick="changeQty('${id}', 1)">+</button>
            </div>
        </div>`;
    }
    container.innerHTML = html;
    updateTotals(subtotal);
}

function changeQty(id, delta) {
    cart[id].qty += delta;
    if (cart[id].qty <= 0) delete cart[id];
    renderCart();
}

function updateTotals(subtotal) {
    const tax = subtotal * 0.11;
    orderTotal = subtotal + tax;
    document.getElementById('subtotalDisplay').textContent = 'Rp ' + formatNum(subtotal);
    document.getElementById('taxDisplay').textContent = 'Rp ' + formatNum(tax);
    document.getElementById('totalDisplay').textContent = 'Rp ' + formatNum(orderTotal);
    calcChange();
}

function calcChange() {
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const change = paid - orderTotal;
    const el = document.getElementById('changeDisplay');
    if (paid > 0) {
        el.textContent = change >= 0 ? `Change: Rp ${formatNum(change)}` : `Insufficient: Rp ${formatNum(Math.abs(change))}`;
        el.style.color = change >= 0 ? 'var(--success)' : 'var(--danger)';
    } else { el.textContent = ''; }
}

function formatNum(n) { return Math.round(n).toLocaleString('id-ID'); }

function clearCart() { cart = {}; renderCart(); }

function setOrderType(type, btn) {
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('orderTypeInput').value = type;
    document.getElementById('tableSection').style.display = type === 'dine-in' ? 'block' : 'none';
}

function selectTable(el) {
    document.querySelectorAll('.table-opt').forEach(t => t.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('tableIdInput').value = el.dataset.table;
}

function filterCategory(catId, btn) {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.menu-item').forEach(item => {
        item.style.display = catId === 'all' || item.dataset.category == catId ? 'block' : 'none';
    });
}

function filterMenu() {
    const q = document.getElementById('menuSearch').value.toLowerCase();
    document.querySelectorAll('.menu-item').forEach(item => {
        item.style.display = item.dataset.name.toLowerCase().includes(q) ? 'block' : 'none';
    });
}

function prepareSubmit() {
    if (Object.keys(cart).length === 0) { alert('Please add items to the order!'); return false; }
    const items = Object.values(cart).map(i => ({ menu_id: i.id, quantity: i.qty, price: i.price, subtotal: i.price * i.qty }));
    document.getElementById('itemsInput').value = JSON.stringify(items);
    return true;
}
</script>
@endpush
