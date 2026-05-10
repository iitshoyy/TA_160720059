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

    <div class="qr-wrap">
        <!-- QR code placeholder - in production use simplesoftwareio/simple-qrcode -->
        <svg width="180" height="180" viewBox="0 0 180 180" xmlns="http://www.w3.org/2000/svg">
            <rect width="180" height="180" fill="white"/>
            <!-- Finder patterns -->
            <rect x="10" y="10" width="50" height="50" fill="black"/>
            <rect x="16" y="16" width="38" height="38" fill="white"/>
            <rect x="22" y="22" width="26" height="26" fill="black"/>
            <rect x="120" y="10" width="50" height="50" fill="black"/>
            <rect x="126" y="16" width="38" height="38" fill="white"/>
            <rect x="132" y="22" width="26" height="26" fill="black"/>
            <rect x="10" y="120" width="50" height="50" fill="black"/>
            <rect x="16" y="126" width="38" height="38" fill="white"/>
            <rect x="22" y="132" width="26" height="26" fill="black"/>
            <!-- Data modules (simplified) -->
            <rect x="70" y="10" width="6" height="6" fill="black"/>
            <rect x="82" y="10" width="6" height="6" fill="black"/>
            <rect x="94" y="10" width="6" height="6" fill="black"/>
            <rect x="106" y="10" width="6" height="6" fill="black"/>
            <rect x="70" y="22" width="6" height="6" fill="black"/>
            <rect x="82" y="22" width="6" height="6" fill="black"/>
            <rect x="106" y="22" width="6" height="6" fill="black"/>
            <rect x="70" y="34" width="6" height="6" fill="black"/>
            <rect x="94" y="34" width="6" height="6" fill="black"/>
            <rect x="106" y="34" width="6" height="6" fill="black"/>
            <rect x="70" y="46" width="6" height="6" fill="black"/>
            <rect x="82" y="46" width="6" height="6" fill="black"/>
            <rect x="94" y="46" width="6" height="6" fill="black"/>
            <rect x="10" y="70" width="6" height="6" fill="black"/>
            <rect x="22" y="70" width="6" height="6" fill="black"/>
            <rect x="46" y="70" width="6" height="6" fill="black"/>
            <rect x="70" y="70" width="6" height="6" fill="black"/>
            <rect x="82" y="70" width="6" height="6" fill="black"/>
            <rect x="94" y="70" width="6" height="6" fill="black"/>
            <rect x="118" y="70" width="6" height="6" fill="black"/>
            <rect x="130" y="70" width="6" height="6" fill="black"/>
            <rect x="154" y="70" width="6" height="6" fill="black"/>
            <rect x="10" y="82" width="6" height="6" fill="black"/>
            <rect x="34" y="82" width="6" height="6" fill="black"/>
            <rect x="58" y="82" width="6" height="6" fill="black"/>
            <rect x="82" y="82" width="6" height="6" fill="black"/>
            <rect x="106" y="82" width="6" height="6" fill="black"/>
            <rect x="130" y="82" width="6" height="6" fill="black"/>
            <rect x="154" y="82" width="6" height="6" fill="black"/>
            <rect x="70" y="94" width="6" height="6" fill="black"/>
            <rect x="82" y="94" width="6" height="6" fill="black"/>
            <rect x="118" y="94" width="6" height="6" fill="black"/>
            <rect x="142" y="94" width="6" height="6" fill="black"/>
            <rect x="70" y="106" width="6" height="6" fill="black"/>
            <rect x="94" y="106" width="6" height="6" fill="black"/>
            <rect x="118" y="106" width="6" height="6" fill="black"/>
            <rect x="130" y="106" width="6" height="6" fill="black"/>
            <rect x="154" y="106" width="6" height="6" fill="black"/>
            <rect x="70" y="118" width="6" height="6" fill="black"/>
            <rect x="82" y="118" width="6" height="6" fill="black"/>
            <rect x="106" y="118" width="6" height="6" fill="black"/>
            <rect x="118" y="118" width="6" height="6" fill="black"/>
            <rect x="142" y="118" width="6" height="6" fill="black"/>
            <rect x="70" y="130" width="6" height="6" fill="black"/>
            <rect x="94" y="130" width="6" height="6" fill="black"/>
            <rect x="106" y="130" width="6" height="6" fill="black"/>
            <rect x="130" y="130" width="6" height="6" fill="black"/>
            <rect x="70" y="142" width="6" height="6" fill="black"/>
            <rect x="82" y="142" width="6" height="6" fill="black"/>
            <rect x="106" y="142" width="6" height="6" fill="black"/>
            <rect x="118" y="142" width="6" height="6" fill="black"/>
            <rect x="142" y="142" width="6" height="6" fill="black"/>
            <rect x="154" y="142" width="6" height="6" fill="black"/>
            <rect x="70" y="154" width="6" height="6" fill="black"/>
            <rect x="94" y="154" width="6" height="6" fill="black"/>
            <rect x="118" y="154" width="6" height="6" fill="black"/>
            <rect x="154" y="154" width="6" height="6" fill="black"/>
        </svg>
    </div>

    <div class="url">{{ $url }}</div>

    <div class="instructions">
        <strong>How to order:</strong>
        <ol style="padding-left:16px;margin-top:6px;">
            <li>Scan this QR code with your phone camera</li>
            <li>Browse the menu and select items</li>
            <li>Tap <strong>Place Order</strong></li>
            <li>Your order goes directly to the kitchen!</li>
        </ol>
    </div>

    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Print QR Code</button><br>
        <a href="{{ route('tables.index') }}" style="display:inline-block;margin-top:10px;color:#666;font-size:.8rem;">← Back to Tables</a>
    </div>
</div>
</body>
</html>
