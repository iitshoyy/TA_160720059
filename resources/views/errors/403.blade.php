{{--
    403 — Forbidden / unauthorized for the current role.

    The middleware aborts with a composed Indonesian message that names the
    roles that *would* be allowed (e.g. "harus login dulu sebagai admin atau chef").
    If $exception is missing for some reason we fall back to a sensible default.
--}}
@php
    $message = ($exception ?? null)?->getMessage();
    if (! $message || $message === 'Forbidden') {
        $message = 'Tidak bisa masuk sini ya... harus login dulu sebagai admin atau chef';
    }
    $userRole = Auth::check() ? (Auth::user()->role ?? 'Staff') : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak — RestoPOS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f0e0c; --surface: #1a1814; --surface2: #221f1a;
            --border: #2e2b25; --gold: #c9a84c; --gold-light: #e8c96d;
            --cream: #f5f0e8; --muted: #6b6455; --text: #e8e0d0;
            --danger: #c0392b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: radial-gradient(ellipse at top, #1a1814 0%, #0f0e0c 70%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 540px;
            width: 100%;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--danger), transparent);
        }
        .icon-wrap {
            width: 88px; height: 88px;
            border-radius: 50%;
            background: rgba(192,57,43,0.12);
            border: 2px solid rgba(192,57,43,0.4);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.4rem;
            color: var(--danger);
        }
        .code {
            font-family: 'Playfair Display', serif;
            font-size: 4.2rem;
            font-weight: 700;
            color: var(--gold);
            line-height: 1;
            letter-spacing: 4px;
        }
        .code-sub {
            font-size: 0.7rem;
            color: var(--muted);
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-top: 4px;
            margin-bottom: 24px;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: var(--cream);
            margin-bottom: 14px;
            font-weight: 600;
        }
        .message {
            color: var(--text);
            font-size: 1.02rem;
            line-height: 1.6;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .current-role {
            font-size: 0.82rem;
            color: var(--muted);
            margin-bottom: 28px;
        }
        .current-role strong { color: var(--gold); font-weight: 600; }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-gold { background: var(--gold); color: #000; }
        .btn-gold:hover { background: var(--gold-light); }
        .btn-outline { background: transparent; color: var(--text); border: 1px solid var(--border); }
        .btn-outline:hover { border-color: var(--gold); color: var(--gold); }
        .btn-danger-outline { background: transparent; color: #e07060; border: 1px solid rgba(192,57,43,0.4); }
        .btn-danger-outline:hover { background: rgba(192,57,43,0.12); }
        .brand {
            position: absolute;
            top: 24px; left: 24px;
            font-family: 'Playfair Display', serif;
            font-size: 0.9rem;
            color: var(--gold);
            letter-spacing: 2px;
        }
        @media (max-width: 480px) {
            .card { padding: 36px 24px; }
            .code { font-size: 3.2rem; }
            h1 { font-size: 1.15rem; }
            .message { font-size: 0.92rem; }
        }
    </style>
</head>
<body>
    <div class="brand">RestoPOS</div>

    <div class="card">
        <div class="icon-wrap">
            <i class="fas fa-lock"></i>
        </div>

        <div class="code">403</div>
        <div class="code-sub">Akses Ditolak</div>

        <h1>Eits, area ini terkunci</h1>

        <div class="message">
            <i class="fas fa-circle-exclamation" style="color: var(--danger); margin-right:6px;"></i>
            {{ $message }}
        </div>

        @if($userRole)
            <div class="current-role">
                Kamu sedang login sebagai <strong>{{ $userRole }}</strong>.
                Logout dulu untuk masuk dengan akun lain.
            </div>
        @endif

        <div class="actions">
            <a href="{{ url()->previous() === url()->current() ? route('dashboard') : url()->previous() }}"
               class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-gold">
                    <i class="fas fa-house"></i> Ke Dashboard Saya
                </a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger-outline">
                        <i class="fas fa-right-from-bracket"></i> Logout & Ganti Akun
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-gold">
                    <i class="fas fa-right-to-bracket"></i> Login
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
