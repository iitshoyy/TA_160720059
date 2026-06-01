<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Reservation — RestoPOS</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:#f5f2ec;color:#2c2416;min-height:100vh;font-size:14px}
        .hero{background:#1a1814;color:#fff;padding:32px 20px;text-align:center}
        .hero-title{font-size:1.4rem;color:#c9a84c;margin-bottom:4px;font-weight:700}
        .hero-sub{color:#8a7d6b;font-size:.85rem}
        .container{max-width:560px;margin:0 auto;padding:24px 16px}
        .card{background:#fff;border:1px solid #e8e0d0;padding:24px}
        .form-group{margin-bottom:14px}
        label{display:block;font-size:.82rem;color:#6b6455;margin-bottom:4px}
        input,select,textarea{width:100%;padding:8px 12px;border:1px solid #e8e0d0;font-size:.9rem;font-family:inherit;color:#2c2416;background:#fff}
        input:focus,select:focus,textarea:focus{outline:none;border-color:#c9a84c}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .btn{width:100%;padding:10px;background:#c9a84c;color:#000;border:1px solid #c9a84c;font-size:.95rem;font-weight:600;cursor:pointer;font-family:inherit}
        .btn:hover{background:#b8960a;border-color:#b8960a}
        .success{color:#155724;border:1px solid #155724;padding:10px 14px;margin-bottom:16px;font-size:.875rem}
        .section-title{font-size:1.05rem;color:#2c2416;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #e8e0d0;font-weight:600}
        .info-box{border:1px solid #e8e0d0;padding:12px;margin-bottom:16px;font-size:.82rem;color:#6b6455;line-height:1.6}
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
