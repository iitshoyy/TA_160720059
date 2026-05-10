@extends('layouts.app')
@section('title', 'New Order')
@section('page-title', 'New Transaction')

@push('styles')
<style>
.pos-grid { display: grid; grid-template-columns: 1fr 380px; gap: 20px; height: calc(100vh - 160px); }
.menu-panel { display: flex; flex-direction: column; overflow: hidden; }
.order-panel { display: flex; flex-direction: column; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.search-bar { padding: 16px; border-bottom: 1px solid var(--border); }
.category-tabs { display: flex; gap: 8px; padding: 12px 16px; overflow-x: auto; border-bottom: 1px solid var(--border); }
.cat-btn { padding: 6px 16px; border-radius: 20px; border: 1px solid var(--border); background: transparent; color: var(--muted); font-size: 0.8rem; cursor: pointer; white-space: nowrap; font-family: 'DM Sans', sans-serif; transition: all 0.15s; }
.cat-btn.active, .cat-btn:hover { background: var(--gold); color: #000; border-color: var(--gold); }
.menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px,1fr)); gap: 12px; padding: 16px; overflow-y: auto; flex: 1; }
.menu-item {
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
}
.menu-item:hover { border-color: var(--gold); transform: translateY(-2px); }
.menu-item.unavailable { opacity: 0.4; cursor: not-allowed; }
.menu-item .item-name { font-size: 0.82rem; font-weight: 600; color: var(--cream); margin: 8px 0 4px; }
.menu-item .item-price { font-size: 0.78rem; color: var(--gold); }
.menu-item .item-emoji { font-size: 2rem; }
.order-header { padding: 16px 20px; border-bottom: 1px solid var(--border); }
.order-header h3 { font-family: 'Playfair Display', serif; font-size: 1rem; color: var(--cream); }
.order-type-btns { display: flex; gap: 6px; margin-top: 10px; }
.type-btn { flex: 1; padding: 7px; border: 1px solid var(--border); background: transparent; color: var(--muted); font-size: 0.75rem; border-radius: 6px; cursor: pointer; font-family: 'DM Sans', sans-serif; }
.type-btn.active { background: var(--gold); color: #000; border-color: var(--gold); font-weight: 600; }
.order-items { flex: 1; overflow-y: auto; padding: 12px; }
.order-item { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; margin-bottom: 6px; background: var(--surface2); }
.order-item .item-info { flex: 1; }
.order-item .item-info .name { font-size: 0.82rem; font-weight: 500; color: var(--cream); }
.order-item .item-info .price { font-size: 0.75rem; color: var(--gold); }
.qty-ctrl { display: flex; align-items: center; gap: 8px; }
.qty-btn { width: 26px; height: 26px; border-radius: 50%; border: 1px solid var(--border); background: var(--surface); color: var(--text); cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; }
.qty-btn:hover { border-color: var(--gold); color: var(--gold); }
.qty-num { font-weight: 600; font-size: 0.85rem; min-width: 20px; text-align: center; }
.order-footer { padding: 16px 20px; border-top: 1px solid var(--border); }
.totals-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--muted); margin-bottom: 6px; }
.totals-row.grand { color: var(--cream); font-weight: 700; font-size: 1rem; padding-top: 8px; border-top: 1px solid var(--border); margin-top: 8px; }
.table-select-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(70px,1fr)); gap: 8px; max-height: 200px; overflow-y: auto; }
.table-opt { padding: 10px 6px; border: 1px solid var(--border); border-radius: 8px; text-align: center; cursor: pointer; font-size: 0.78rem; transition: all 0.15s; }
.table-opt:hover:not(.occupied) { border-color: var(--gold); color: var(--gold); }
.table-opt.selected { background: var(--gold); color: #000; border-color: var(--gold); font-weight: 600; }
.table-opt.occupied { opacity: 0.3; cursor: not-allowed; }
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
    <div class="menu-panel card" style="padding:0;">
        <div class="search-bar">
            <input type="text" id="menuSearch" class="form-control" placeholder="🔍 Search menu items..." oninput="filterMenu()">
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
                <div class="item-name">{{ $menu->name }}</div>
                <div class="item-price">Rp {{ number_format($menu->price) }}</div>
                @if(!$menu->availability)
                <div style="font-size:0.65rem; color:var(--danger); margin-top:4px;">Unavailable</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- RIGHT: Order Panel -->
    <div class="order-panel">
        <div class="order-header">
            <h3><i class="fas fa-receipt" style="color:var(--gold); margin-right:6px;"></i> Current Order</h3>
            <div class="order-type-btns">
                <button type="button" class="type-btn active" onclick="setOrderType('dine-in', this)">🪑 Dine-In</button>
                <button type="button" class="type-btn" onclick="setOrderType('takeaway', this)">🥡 Takeaway</button>
                <button type="button" class="type-btn" onclick="setOrderType('pre-order', this)">📋 Pre-Order</button>
            </div>
        </div>

        <!-- Table Selection (for dine-in) -->
        <div id="tableSection" style="padding:12px; border-bottom:1px solid var(--border);">
            <div style="font-size:0.72rem; color:var(--muted); letter-spacing:2px; text-transform:uppercase; margin-bottom:8px;">Select Table</div>
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
        <div style="padding:10px 12px; border-bottom:1px solid var(--border);">
            <input type="text" name="customer_name" class="form-control" placeholder="Customer name (optional)">
        </div>

        <!-- Order Items -->
        <div class="order-items" id="orderItems">
            <div id="emptyCart" style="text-align:center; color:var(--muted); padding:40px 20px;">
                <div style="font-size:3rem; margin-bottom:10px;">🛒</div>
                <div>Tap menu items to add to order</div>
            </div>
        </div>

        <!-- Totals & Checkout -->
        <div class="order-footer">
            <div class="totals-row"><span>Subtotal</span><span id="subtotalDisplay">Rp 0</span></div>
            <div class="totals-row"><span>Tax (11%)</span><span id="taxDisplay">Rp 0</span></div>
            <div class="totals-row grand"><span>TOTAL</span><span id="totalDisplay">Rp 0</span></div>
            <div class="form-group" style="margin:14px 0 10px;">
                <label class="form-label">Payment Method</label>
                <select name="payment_type" class="form-control">
                    <option value="cash">Cash</option>
                    <option value="transfer">Bank Transfer</option>
                    <option value="qris">QRIS</option>
                    <option value="card">Debit/Credit Card</option>
                </select>
            </div>
            <div id="cashInput" style="margin-bottom:10px;">
                <input type="number" name="amount_paid" id="amountPaid" class="form-control" placeholder="Amount paid" oninput="calcChange()">
                <div id="changeDisplay" style="font-size:0.8rem; color:var(--gold); margin-top:5px;"></div>
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%; justify-content:center; padding:13px;" onclick="return prepareSubmit()">
                <i class="fas fa-check-circle"></i> Place Order
            </button>
            <button type="button" class="btn btn-outline" style="width:100%; justify-content:center; margin-top:8px;" onclick="clearCart()">
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
