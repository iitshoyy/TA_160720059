<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }} — Status | RestoPOS</title>
    <style>
        :root{--bg:#faf8f4;--surface:#fff;--border:#e8e0d0;--gold:#b8860b;--text:#2c2416;--muted:#8a7d6b;--success:#16a34a;--danger:#dc2626}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;font-size:14px}
        .header{background:var(--text);color:#fff;padding:14px 16px;display:flex;align-items:center;justify-content:space-between}
        .header .name{font-weight:700;font-size:1rem}
        .table-badge{background:var(--gold);color:#fff;padding:2px 10px;font-size:.78rem;font-weight:600}
        .wrap{max-width:480px;margin:0 auto;padding:20px 16px 60px}
        .order-no{font-size:1.3rem;font-weight:700}
        .sub{color:var(--muted);font-size:.85rem;margin-top:2px}
        .steps{margin:24px 0;display:flex;flex-direction:column;gap:0}
        .step{display:flex;gap:12px;align-items:flex-start;padding-bottom:20px;position:relative}
        .step:not(:last-child)::before{content:"";position:absolute;left:13px;top:28px;bottom:0;width:1px;background:var(--border)}
        .step.done:not(:last-child)::before{background:var(--success)}
        .dot{width:26px;height:26px;border:1px solid var(--border);background:var(--surface);display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;z-index:1;font-weight:600}
        .step.done .dot{background:var(--success);border-color:var(--success);color:#fff}
        .step.active .dot{background:var(--gold);border-color:var(--gold);color:#fff}
        .step .body .t{font-weight:600;font-size:.95rem}
        .step .body .d{color:var(--muted);font-size:.82rem;margin-top:2px}
        .step:not(.done):not(.active) .body{opacity:.5}
        .card{background:var(--surface);border:1px solid var(--border);padding:14px;margin-top:8px}
        .row{display:flex;justify-content:space-between;font-size:.88rem;padding:4px 0}
        .row.total{font-weight:700;border-top:1px solid var(--text);margin-top:6px;padding-top:8px;font-size:.95rem}
        .cancelled{color:var(--danger);border:1px solid var(--danger);padding:12px;text-align:center;font-weight:600;margin-top:8px}
        .footnote{color:var(--muted);font-size:.78rem;text-align:center;margin-top:20px}
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
