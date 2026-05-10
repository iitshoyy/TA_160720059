<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AhoyPOS — Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'DM Sans',sans-serif;background:#0f0e0c;min-height:100vh;display:flex;align-items:center;justify-content:center}
        .card{background:#1a1814;border:1px solid #2e2b25;border-radius:20px;padding:48px 40px;width:100%;max-width:420px}
        .logo{font-family:'Playfair Display',serif;font-size:2rem;color:#c9a84c;text-align:center;margin-bottom:6px}
        .sub{text-align:center;color:#6b6455;font-size:0.8rem;letter-spacing:3px;text-transform:uppercase;margin-bottom:36px}
        .form-group{margin-bottom:18px}
        label{display:block;font-size:0.75rem;color:#6b6455;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:6px}
        input{width:100%;background:#221f1a;border:1px solid #2e2b25;color:#e8e0d0;padding:12px 16px;border-radius:10px;font-size:0.9rem;font-family:'DM Sans',sans-serif;transition:border-color .15s}
        input:focus{outline:none;border-color:#c9a84c}
        .btn{width:100%;padding:14px;background:#c9a84c;color:#000;border:none;border-radius:10px;font-size:0.95rem;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:6px;transition:background .15s}
        .btn:hover{background:#e8c96d}
        .error{background:rgba(192,57,43,.12);border:1px solid rgba(192,57,43,.3);color:#e07060;padding:10px 14px;border-radius:8px;font-size:0.82rem;margin-bottom:16px}
        .remember{display:flex;align-items:center;gap:8px;font-size:0.82rem;color:#6b6455;margin-bottom:20px}
        .hint{text-align:center;color:#6b6455;font-size:0.78rem;margin-top:24px;line-height:1.6}
        .hint code{color:#c9a84c;background:#221f1a;padding:2px 6px;border-radius:4px}
    </style>
</head>
<body>
<div class="card">
    <div class="logo">🍽️ AhoyPOS</div>
    <div class="sub">Restaurant Management System</div>

    @if($errors->any())
    <div class="error">{{ $errors->first() }}</div>
    @endif
    @if(session('success'))
    <div style="background:rgba(39,174,96,.12);border:1px solid rgba(39,174,96,.3);color:#5dba82;padding:10px 14px;border-radius:8px;font-size:.82rem;margin-bottom:16px">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="Enter username">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter password">
        </div>
        <div class="remember">
            <input type="checkbox" name="remember" id="remember" style="width:auto;accent-color:#c9a84c">
            <label for="remember" style="text-transform:none;letter-spacing:0;font-size:.82rem;cursor:pointer">Remember me</label>
        </div>
        <button type="submit" class="btn">Sign In</button>
    </form>
    <div class="hint">
        Default login: <code>admin</code> / <code>admin123</code><br>
        Or: <code>kasir</code> / <code>kasir123</code>
    </div>
</div>
</body>
</html>
