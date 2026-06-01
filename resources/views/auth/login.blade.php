<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AhoyPOS — Sign In</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:#0f0e0c;color:#e8e0d0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{background:#1a1814;border:1px solid #2e2b25;padding:32px;width:100%;max-width:380px}
        .logo{font-size:1.4rem;color:#c9a84c;text-align:center;margin-bottom:4px;font-weight:700}
        .sub{text-align:center;color:#6b6455;font-size:0.78rem;margin-bottom:24px}
        .form-group{margin-bottom:14px}
        label{display:block;font-size:0.8rem;color:#6b6455;margin-bottom:4px}
        input{width:100%;background:#221f1a;border:1px solid #2e2b25;color:#e8e0d0;padding:8px 12px;font-size:0.9rem;font-family:inherit}
        input:focus{outline:none;border-color:#c9a84c}
        .btn{width:100%;padding:10px;background:#c9a84c;color:#000;border:1px solid #c9a84c;font-size:0.9rem;font-weight:600;cursor:pointer;font-family:inherit;margin-top:4px}
        .btn:hover{background:#e8c96d;border-color:#e8c96d}
        .error{color:#e07060;border:1px solid #e07060;padding:8px 12px;font-size:0.82rem;margin-bottom:14px}
        .remember{display:flex;align-items:center;gap:8px;font-size:0.82rem;color:#6b6455;margin-bottom:16px}
        .hint{text-align:center;color:#6b6455;font-size:0.78rem;margin-top:18px;line-height:1.6}
        .hint code{color:#c9a84c;background:#221f1a;padding:1px 5px}
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
    <div style="color:#5dba82;border:1px solid #5dba82;padding:8px 12px;font-size:.82rem;margin-bottom:14px">{{ session('success') }}</div>
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
            <label for="remember" style="font-size:.82rem;cursor:pointer;margin-bottom:0">Remember me</label>
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
