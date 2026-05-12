<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Table QR Codes — RestoPOS</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Georgia',serif;background:#fff;color:#000;padding:24px}
        .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
        .top h1{font-size:1.3rem}
        .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
        .card{border:2px solid #000;border-radius:10px;padding:18px;text-align:center;break-inside:avoid}
        .card .tn{font-size:1.3rem;font-weight:bold;margin-bottom:8px}
        .card .cap{color:#666;font-size:.8rem;margin-bottom:10px}
        .card .qr{display:inline-block}
        .card .qr svg{width:150px;height:150px}
        .card .u{font-size:.62rem;color:#666;word-break:break-all;margin-top:8px;font-family:monospace}
        .btn{display:inline-block;padding:8px 18px;background:#000;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:.85rem;text-decoration:none}
        @media print{.no-print{display:none}body{padding:0}.grid{gap:8px}}
    </style>
</head>
<body>
@php $appHost = parse_url(config('app.url'), PHP_URL_HOST); @endphp
<div class="top">
    <h1>🍽️ RestoPOS — Table QR Codes</h1>
    <div class="no-print">
        <a href="{{ route('tables.index') }}" class="btn" style="background:#666;">← Tables</a>
        <button class="btn" onclick="window.print()">🖨️ Print</button>
    </div>
</div>

@if(in_array($appHost, ['localhost', '127.0.0.1', null], true))
<div class="no-print" style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:8px;padding:10px 12px;font-size:.8rem;margin-bottom:16px;">
    APP_URL is <code>{{ config('app.url') }}</code> — phones can't scan these. Set <code>APP_URL</code> to your LAN IP, run <code>php artisan optimize:clear</code>, then reload this page before printing.
</div>
@endif

<div class="grid">
    @forelse($tables as $t)
    <div class="card">
        <div class="tn">{{ $t->name }}</div>
        <div class="cap">Capacity: {{ $t->capacity }} pax</div>
        <div class="qr">{!! $t->qr_svg !!}</div>
        <div class="u">{{ $t->qr_url }}</div>
    </div>
    @empty
    <div>No tables yet.</div>
    @endforelse
</div>
</body>
</html>
