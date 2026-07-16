<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order — {{ $table->name ?? 'Table' }} | RestoPOS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --bg: #faf8f4;
            --surface: #ffffff;
            --border: #e8e0d0;
            --gold: #b8860b;
            --text: #2c2416;
            --muted: #8a7d6b;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); font-size: 14px; }

        .header {
            background: var(--text);
            color: #fff;
            padding: 14px 16px;
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header .restaurant-name { font-weight: 700; font-size: 1rem; }
        .header .table-badge {
            background: var(--gold);
            color: #fff;
            padding: 2px 10px;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .cart-float {
            position: fixed;
            bottom: 16px;
            left: 16px;
            right: 16px;
            background: var(--text);
            color: #fff;
            padding: 12px 16px;
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            cursor: pointer;
            z-index: 40;
            font-weight: 600;
            border: 1px solid var(--text);
        }
        .cart-float.visible { display: flex; }
        .cart-badge {
            background: var(--gold);
            color: #fff;
            width: 22px; height: 22px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 700;
        }
        .categories {
            display: flex;
            gap: 6px;
            padding: 10px 16px;
            overflow-x: auto;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            scrollbar-width: none;
        }
        .categories::-webkit-scrollbar { display: none; }
        .cat-pill {
            padding: 5px 12px;
            border: 1px solid var(--border);
            background: transparent;
            font-size: 0.82rem;
            cursor: pointer;
            white-space: nowrap;
            font-family: inherit;
            color: var(--muted);
        }
        .cat-pill.active { background: var(--text); color: #fff; border-color: var(--text); }
        .section-title { padding: 16px 16px 8px; font-weight: 600; font-size: 0.95rem; color: var(--text); }
        .menu-list { padding: 0 16px; display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
        .menu-card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 12px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .menu-emoji { font-size: 1.8rem; flex-shrink: 0; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; text-align: center; }
        .menu-photo { width: 48px; height: 48px; flex-shrink: 0; object-fit: cover; border-radius: 6px; border: 1px solid var(--border); }
        .menu-info { flex: 1; }
        .menu-info .name { font-weight: 600; font-size: 0.92rem; }
        .menu-info .desc { font-size: 0.78rem; color: var(--muted); margin-top: 2px; }
        .menu-info .price { font-weight: 600; color: var(--gold); font-size: 0.88rem; margin-top: 4px; }
        .qty-control { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .qty-btn {
            width: 28px; height: 28px;
            border: 1px solid var(--border);
            background: var(--bg);
            font-size: 1rem;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-family: inherit;
            color: var(--text);
        }
        .qty-btn.add { background: var(--text); color: #fff; border-color: var(--text); }
        .qty-num { font-weight: 600; min-width: 18px; text-align: center; font-size: 0.9rem; }

        /* Cart Drawer */
        .cart-drawer { position: fixed; inset: 0; z-index: 100; display: none; }
        .cart-drawer.open { display: block; }
        .cart-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); }
        .cart-panel {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 20px 16px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .cart-handle { display: none; }
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); }
        .cart-item:last-of-type { border-bottom: none; }
        .cart-total { font-size: 1rem; font-weight: 700; padding: 12px 0 0; display: flex; justify-content: space-between; border-top: 1px solid var(--text); margin-top: 4px; }
        .order-btn {
            width: 100%;
            margin-top: 12px;
            padding: 12px;
            background: var(--text);
            color: #fff;
            border: 1px solid var(--text);
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .notes-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border);
            font-family: inherit;
            font-size: 0.85rem;
            margin-top: 10px;
            color: var(--text);
            background: var(--bg);
        }
        .notes-input:focus { outline: none; border-color: var(--gold); }
    </style>
</head>
<body>

<div class="header">
    <div>
        <div class="restaurant-name">🍽️ RestoPOS</div>
        <div style="font-size:0.75rem; opacity:0.7; margin-top:2px;">Scan & Order</div>
    </div>
    <div class="table-badge">{{ $table->name ?? 'Table' }}</div>
</div>

<div class="categories">
    <button class="cat-pill active" onclick="filterCat('all', this)">All</button>
    @foreach($categories ?? [] as $cat)
    <button class="cat-pill" onclick="filterCat('{{ $cat->id }}', this)">{{ $cat->name }}</button>
    @endforeach
</div>

@foreach($categories ?? [] as $cat)
@php $catMenus = $menus->where('categoryMenus_id', $cat->id); @endphp
@if($catMenus->count() > 0)
<div class="category-section" data-cat="{{ $cat->id }}">
    <div class="section-title">{{ $cat->name }}</div>
    <div class="menu-list">
        @foreach($catMenus as $menu)
        @php($cap = $menu->stockCapacity())
        <div class="menu-card" @if($cap <= 0) style="opacity:0.5;" @endif>
            @if($menu->image)
            <img class="menu-photo" src="{{ asset('storage/'.$menu->image) }}" alt="{{ $menu->name }}">
            @else
            <div class="menu-emoji">🍽️</div>
            @endif
            <div class="menu-info">
                <div class="name">{{ $menu->name }}</div>
                @if($menu->description)<div class="desc">{{ Str::limit($menu->description, 60) }}</div>@endif
                <div class="price">Rp {{ number_format($menu->price) }}</div>
            </div>
            @if($cap > 0)
            <div class="qty-control">
                <button type="button" class="qty-btn" onclick="removeItem('{{ $menu->id }}')" id="minus-{{ $menu->id }}" style="display:none;">−</button>
                <span class="qty-num" id="qty-{{ $menu->id }}" style="display:none;">0</span>
                <button type="button" class="qty-btn add" onclick="addItem('{{ $menu->id }}', '{{ addslashes($menu->name) }}', {{ $menu->price }}, {{ $cap }})">+</button>
            </div>
            @else
            <div class="qty-control"><span style="font-size:0.75rem;color:var(--muted);">Sold Out</span></div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
@endforeach

<div style="height: 100px;"></div>

<!-- Cart Float Button -->
<div class="cart-float" id="cartFloat" onclick="openCart()">
    <div style="display:flex; align-items:center; gap:10px;">
        <span class="cart-badge" id="cartCount">0</span>
        <span>View Order</span>
    </div>
    <span id="cartTotalFloat">Rp 0</span>
</div>

<!-- Cart Drawer -->
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-overlay" onclick="closeCart()"></div>
    <div class="cart-panel">
        <div class="cart-handle"></div>
        <h3 style="font-weight:700; font-size:1.1rem; margin-bottom:16px;">Your Order</h3>
        <div id="cartItemsList"></div>
        <div class="cart-total">
            <span>Total</span>
            <span id="cartTotalDrawer">Rp 0</span>
        </div>
        <textarea class="notes-input" placeholder="Add notes for the kitchen (optional)..." id="orderNotes" rows="2"></textarea>
        <button class="order-btn" onclick="submitOrder()">
            🛎️ Place Order
        </button>
    </div>
</div>

<script>
let cart = {};

function addItem(id, name, price, cap) {
    if (!cart[id]) cart[id] = { id, name, price, qty: 0, cap };
    if (cart[id].qty + 1 > cap) {
        alert('Only ' + cap + ' portion(s) of ' + name + ' available.');
        return;
    }
    cart[id].qty++;
    document.getElementById('qty-' + id).textContent = cart[id].qty;
    document.getElementById('qty-' + id).style.display = 'inline';
    document.getElementById('minus-' + id).style.display = 'flex';
    updateCartUI();
}

function removeItem(id) {
    if (!cart[id]) return;
    cart[id].qty--;
    if (cart[id].qty <= 0) {
        delete cart[id];
        document.getElementById('qty-' + id).style.display = 'none';
        document.getElementById('minus-' + id).style.display = 'none';
    } else {
        document.getElementById('qty-' + id).textContent = cart[id].qty;
    }
    updateCartUI();
}

function updateCartUI() {
    const items = Object.values(cart);
    const totalQty = items.reduce((s, i) => s + i.qty, 0);
    const total = items.reduce((s, i) => s + i.price * i.qty, 0);
    const fmt = n => Math.round(n).toLocaleString('id-ID');

    document.getElementById('cartCount').textContent = totalQty;
    document.getElementById('cartTotalFloat').textContent = 'Rp ' + fmt(total);
    document.getElementById('cartTotalDrawer').textContent = 'Rp ' + fmt(total);
    document.getElementById('cartFloat').classList.toggle('visible', totalQty > 0);

    let html = '';
    items.forEach(item => {
        html += `<div class="cart-item">
            <div>
                <div style="font-weight:600; font-size:0.9rem;">${item.name}</div>
                <div style="font-size:0.78rem; color:var(--muted);">Rp ${fmt(item.price)} × ${item.qty}</div>
            </div>
            <div style="font-weight:700; color:var(--gold);">Rp ${fmt(item.price * item.qty)}</div>
        </div>`;
    });
    document.getElementById('cartItemsList').innerHTML = html;
}

function openCart() { document.getElementById('cartDrawer').classList.add('open'); }
function closeCart() { document.getElementById('cartDrawer').classList.remove('open'); }

function filterCat(catId, btn) {
    document.querySelectorAll('.cat-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.category-section').forEach(s => {
        s.style.display = catId === 'all' || s.dataset.cat == catId ? 'block' : 'none';
    });
}

async function submitOrder() {
    const items = Object.values(cart).map(i => ({ menu_id: i.id, quantity: i.qty, price: i.price, subtotal: i.price * i.qty }));
    if (items.length === 0) return;

    try {
        const resp = await fetch('{{ route("customer.order.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                table_id: {{ $table->id ?? 'null' }},
                items,
                notes: document.getElementById('orderNotes').value
            })
        });
        if (resp.ok) {
            const data = await resp.json();
            window.location.href = data.status_url;
        } else {
            let msg = 'Failed to place order. Please try again or call our staff.';
            try { const data = await resp.json(); if (data.message) msg = data.message; } catch (e) {}
            alert(msg);
        }
    } catch (e) {
        alert('Network error. Please try again.');
    }
}
</script>
</body>
</html>
