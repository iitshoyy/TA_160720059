<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Reservation — RestoPOS</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:#f5f2ec;min-height:100vh}
        .hero{background:#1a1814;color:#fff;padding:48px 20px;text-align:center}
        .hero-title{font-family:'Playfair Display',serif;font-size:2rem;color:#c9a84c;margin-bottom:8px}
        .hero-sub{color:#8a7d6b;font-size:.9rem}
        .container{max-width:600px;margin:0 auto;padding:32px 20px}
        .card{background:#fff;border-radius:16px;padding:32px;box-shadow:0 4px 24px rgba(0,0,0,.08)}
        .form-group{margin-bottom:18px}
        label{display:block;font-size:.78rem;color:#6b6455;letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;font-weight:500}
        input,select,textarea{width:100%;padding:12px 14px;border:1.5px solid #e8e0d0;border-radius:10px;font-size:.9rem;font-family:inherit;transition:border-color .15s;color:#2c2416;background:#faf8f4}
        input:focus,select:focus,textarea:focus{outline:none;border-color:#c9a84c;background:#fff}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .btn{width:100%;padding:14px;background:#c9a84c;color:#000;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;font-family:inherit;transition:background .15s}
        .btn:hover{background:#b8960a}
        .success{background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:16px;border-radius:10px;margin-bottom:20px;font-size:.875rem}
        .section-title{font-family:'Playfair Display',serif;font-size:1.2rem;color:#2c2416;margin-bottom:20px;padding-bottom:10px;border-bottom:2px solid #e8e0d0}
        .info-box{background:#faf8f4;border:1px solid #e8e0d0;border-radius:10px;padding:16px;margin-bottom:20px;font-size:.82rem;color:#6b6455;line-height:1.7}
        @media(max-width:480px){.form-row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="hero">
    <div class="hero-title">🍽️ RestoPOS</div>
    <div class="hero-sub">Online Table Reservation</div>
</div>
<div class="container">
    @if(session('success'))
    <div class="success">✅ {{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="section-title">Book Your Table</div>
        <div class="info-box">
            📞 We'll confirm your reservation via phone/WhatsApp within 30 minutes.<br>
            ⏰ Reservations available daily from 10:00 – 21:00.<br>
            👥 For groups of 10+, please call us directly.
        </div>
        <form method="POST" action="{{ route('reservation.public.store') }}">
        @csrf
            <div class="form-row">
                <div class="form-group"><label>Full Name *</label><input name="customer_name" required placeholder="Your full name" value="{{ old('customer_name') }}"></div>
                <div class="form-group"><label>Phone Number *</label><input name="phone" required placeholder="08xx-xxxx-xxxx" value="{{ old('phone') }}"></div>
            </div>
            <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="Optional" value="{{ old('email') }}"></div>
            <div class="form-row">
                <div class="form-group"><label>Date *</label><input type="date" name="reservation_date" required min="{{ date('Y-m-d') }}" value="{{ old('reservation_date') }}"></div>
                <div class="form-group"><label>Time *</label><input type="time" name="reservation_time" required min="10:00" max="21:00" value="{{ old('reservation_time') }}"></div>
            </div>
            <div class="form-group"><label>Number of Guests *</label>
                <select name="guests" required>
                    @for($i=1;$i<=12;$i++)<option value="{{ $i }}" {{ old('guests')==$i?'selected':'' }}>{{ $i }} {{ $i==1?'person':'people' }}</option>@endfor
                </select>
            </div>
            <div class="form-group"><label>Special Notes / Requests</label><textarea name="notes" rows="3" placeholder="Birthday celebration, dietary restrictions, special seating requests...">{{ old('notes') }}</textarea></div>
            <button type="submit" class="btn">📅 Submit Reservation</button>
        </form>
    </div>
</div>
</body>
</html>
