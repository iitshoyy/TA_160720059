<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code — {{ $table->name }}</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Georgia',serif;background:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
        .card{background:#fff;border:2px solid #000;border-radius:12px;padding:36px;text-align:center;max-width:340px;width:100%}
        .logo{font-size:1.6rem;font-weight:bold;margin-bottom:4px}
        .table-name{font-size:1.8rem;font-weight:bold;margin:12px 0 4px}
        .cap{color:#666;font-size:.9rem;margin-bottom:20px}
        .qr-wrap{background:#f5f5f5;border:1px solid #ddd;border-radius:8px;padding:20px;margin:0 auto 20px;display:inline-block}
        .url{font-size:.72rem;color:#666;word-break:break-all;margin-bottom:20px;font-family:monospace}
        .instructions{background:#f9f7f0;border:1px solid #e8e0c8;border-radius:8px;padding:14px;font-size:.82rem;text-align:left;line-height:1.7}
        .btn{display:inline-block;margin-top:16px;padding:10px 24px;background:#000;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:.9rem;text-decoration:none}
        @media print{.no-print{display:none}}
    </style>
</head>
<body>
<div class="card">
    <div class="logo">🍽️ RestoPOS</div>
    <div class="table-name">{{ $table->name }}</div>
    <div class="cap">Capacity: {{ $table->capacity }} pax</div>

    @php $appHost = parse_url(config('app.url'), PHP_URL_HOST); @endphp
    @if(in_array($appHost, ['localhost', '127.0.0.1', null], true))
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:8px;padding:10px 12px;font-size:.78rem;margin-bottom:14px;text-align:left;">
        <strong>Heads up:</strong> APP_URL is <code>{{ config('app.url') }}</code> — a phone can't reach that.
        Set <code>APP_URL=http://&lt;your-LAN-IP&gt;:8000</code> in <code>.env</code>, run <code>php artisan optimize:clear</code>, and serve with <code>php artisan serve --host=0.0.0.0</code>.
    </div>
    @endif

    <div class="qr-wrap">
        {!! $qr !!}
    </div>

    <div class="url">{{ $url }}</div>

    <div class="instructions">
        <strong>How to order:</strong>
        <ol style="padding-left:16px;margin-top:6px;">
            <li>Scan this QR code with your phone camera</li>
            <li>Browse the menu and select items</li>
            <li>Tap <strong>Place Order</strong></li>
            <li>Pay at the cashier — your order starts once payment is confirmed</li>
        </ol>
    </div>

    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Print QR Code</button><br>
        <a href="{{ route('tables.index') }}" style="display:inline-block;margin-top:10px;color:#666;font-size:.8rem;">← Back to Tables</a>
    </div>
</div>
</body>
</html>
