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
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-size: 14px;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 32px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            position: relative;
        }
        .icon-wrap {
            width: 56px; height: 56px;
            border: 1px solid var(--danger);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.6rem;
            color: var(--danger);
        }
        .code {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--gold);
            line-height: 1;
        }
        .code-sub {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 4px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 1.15rem;
            color: var(--cream);
            margin-bottom: 12px;
            font-weight: 600;
        }
        .message {
            color: var(--text);
            font-size: 0.95rem;
            line-height: 1.5;
            border: 1px solid var(--border);
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .current-role {
            font-size: 0.82rem;
            color: var(--muted);
            margin-bottom: 20px;
        }
        .current-role strong { color: var(--gold); font-weight: 600; }
        .actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            font-family: inherit;
        }
        .btn-gold { background: var(--gold); color: #000; border-color: var(--gold); }
        .btn-gold:hover { background: var(--gold-light); border-color: var(--gold-light); }
        .btn-outline { background: transparent; color: var(--text); border-color: var(--border); }
        .btn-outline:hover { border-color: var(--gold); color: var(--gold); }
        .btn-danger-outline { background: transparent; color: #e07060; border-color: #e07060; }
        .brand {
            position: absolute;
            top: 20px; left: 20px;
            font-size: 0.85rem;
            color: var(--gold);
            font-weight: 600;
        }
        @media (max-width: 480px) {
            .card { padding: 24px; }
            .code { font-size: 2.2rem; }
            h1 { font-size: 1.05rem; }
            .message { font-size: 0.88rem; }
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
