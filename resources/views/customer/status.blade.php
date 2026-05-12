<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }} — Status | RestoPOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#faf8f4;--surface:#fff;--border:#e8e0d0;--gold:#b8860b;--text:#2c2416;--muted:#8a7d6b;--success:#16a34a;--danger:#dc2626}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        .header{background:var(--text);color:#fff;padding:18px 20px;display:flex;align-items:center;justify-content:space-between}
        .header .name{font-weight:700;font-size:1.05rem}
        .table-badge{background:var(--gold);color:#fff;padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:600}
        .wrap{max-width:480px;margin:0 auto;padding:24px 16px 60px}
        .order-no{font-size:1.6rem;font-weight:700;letter-spacing:-.5px}
        .sub{color:var(--muted);font-size:.85rem;margin-top:2px}
        .steps{margin:28px 0;display:flex;flex-direction:column;gap:0}
        .step{display:flex;gap:14px;align-items:flex-start;padding-bottom:24px;position:relative}
        .step:not(:last-child)::before{content:"";position:absolute;left:15px;top:32px;bottom:0;width:2px;background:var(--border)}
        .step.done:not(:last-child)::before{background:var(--success)}
        .dot{width:32px;height:32px;border-radius:50%;border:2px solid var(--border);background:var(--surface);display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;z-index:1}
        .step.done .dot{background:var(--success);border-color:var(--success);color:#fff}
        .step.active .dot{background:var(--gold);border-color:var(--gold);color:#fff;animation:pulse 1.4s infinite}
        @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(184,134,11,.5)}70%{box-shadow:0 0 0 12px rgba(184,134,11,0)}100%{box-shadow:0 0 0 0 rgba(184,134,11,0)}}
        .step .body .t{font-weight:600;font-size:.98rem}
        .step .body .d{color:var(--muted);font-size:.82rem;margin-top:2px}
        .step:not(.done):not(.active) .body{opacity:.5}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:16px;margin-top:8px}
        .row{display:flex;justify-content:space-between;font-size:.88rem;padding:5px 0}
        .row.total{font-weight:700;border-top:2px solid var(--border);margin-top:6px;padding-top:10px;font-size:1rem}
        .cancelled{background:#fef2f2;border:1px solid #fecaca;color:var(--danger);border-radius:12px;padding:14px;text-align:center;font-weight:600;margin-top:8px}
        .footnote{color:var(--muted);font-size:.78rem;text-align:center;margin-top:24px}
    </style>
</head>
<body>
<div class="header">
    <div><div class="name">🍽️ RestoPOS</div><div style="font-size:.74rem;opacity:.7;margin-top:2px;">Order status</div></div>
    @if($order->table)<div class="table-badge">{{ $order->table->name }}</div>@endif
</div>

<div class="wrap">
    <div class="order-no">Order #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</div>
    <div class="sub">{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, H:i') }}</div>

    <div class="steps" id="steps">
        <div class="step" data-step="payment">
            <div class="dot">1</div>
            <div class="body"><div class="t">Awaiting payment</div><div class="d">Please go to the cashier to pay for your order.</div></div>
        </div>
        <div class="step" data-step="processing">
            <div class="dot">2</div>
            <div class="body"><div class="t">Being prepared 🔥</div><div class="d">Payment received — the kitchen is on it.</div></div>
        </div>
        <div class="step" data-step="completed">
            <div class="dot">3</div>
            <div class="body"><div class="t">Ready / completed</div><div class="d">Enjoy your meal!</div></div>
        </div>
    </div>

    <div id="cancelledBox" class="cancelled" style="display:none;">This order was cancelled. Please ask our staff if you need help.</div>

    <div class="card">
        @foreach($order->orderDetails as $d)
        <div class="row"><span>{{ $d->menu->name ?? 'Item' }} × {{ $d->quantity }}</span><span>Rp {{ number_format($d->subtotal) }}</span></div>
        @endforeach
        <div class="row total"><span>Total (incl. 11% tax)</span><span>Rp {{ number_format($order->total_amount) }}</span></div>
    </div>

    <div class="footnote">This page updates automatically.</div>
</div>

<script>
const STATE_URL = "{{ route('customer.order.status.state', $order->id) }}";

function render(status) {
    const order = ['payment', 'processing', 'completed'];
    const map = { pending: 'payment', processing: 'processing', completed: 'completed' };
    const cancelled = status === 'cancelled';
    document.getElementById('cancelledBox').style.display = cancelled ? 'block' : 'none';
    document.getElementById('steps').style.display = cancelled ? 'none' : 'flex';
    if (cancelled) return true;

    const currentIdx = order.indexOf(map[status] ?? 'payment');
    document.querySelectorAll('.step').forEach((el, i) => {
        el.classList.toggle('done', i < currentIdx);
        el.classList.toggle('active', i === currentIdx);
    });
    return status === 'completed';
}

let stop = render(@json($order->status));

async function poll() {
    if (stop) return;
    try {
        const r = await fetch(STATE_URL, { headers: { 'Accept': 'application/json' } });
        if (r.ok) { const d = await r.json(); stop = render(d.status); }
    } catch (e) { /* keep trying */ }
    if (!stop) setTimeout(poll, 5000);
}
if (!stop) setTimeout(poll, 5000);
</script>
</body>
</html>
