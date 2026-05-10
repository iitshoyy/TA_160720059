<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RestoPOS') — Restaurant Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f0e0c;
            --surface: #1a1814;
            --surface2: #221f1a;
            --border: #2e2b25;
            --gold: #c9a84c;
            --gold-light: #e8c96d;
            --cream: #f5f0e8;
            --muted: #6b6455;
            --text: #e8e0d0;
            --danger: #c0392b;
            --success: #27ae60;
            --info: #2980b9;
            --warning: #f39c12;
            --sidebar-w: 260px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            overflow-y: auto;
        }
        .sidebar-logo {
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-logo .brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gold);
            letter-spacing: 1px;
        }
        .sidebar-logo .sub {
            font-size: 0.7rem;
            color: var(--muted);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .sidebar-section {
            padding: 20px 0 0;
        }
        .sidebar-label {
            font-size: 0.65rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0 24px 10px;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 24px;
            color: #8a8070;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.15s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover {
            color: var(--cream);
            background: var(--surface2);
            border-left-color: var(--border);
        }
        .sidebar-nav a.active {
            color: var(--gold);
            background: rgba(201,168,76,0.08);
            border-left-color: var(--gold);
        }
        .sidebar-nav a i { width: 18px; text-align: center; font-size: 0.9rem; }
        .sidebar-footer {
            margin-top: auto;
            padding: 20px 24px;
            border-top: 1px solid var(--border);
            font-size: 0.8rem;
            color: var(--muted);
        }
        .sidebar-footer .user-name { color: var(--cream); font-weight: 500; margin-bottom: 2px; }
        .sidebar-footer .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            color: var(--danger);
            text-decoration: none;
            font-size: 0.8rem;
            transition: opacity 0.15s;
        }
        .sidebar-footer .logout-btn:hover { opacity: 0.7; }

        /* MAIN CONTENT */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .topbar-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            color: var(--cream);
        }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 0.85rem;
            color: var(--muted);
        }
        .badge-pill {
            background: var(--gold);
            color: #000;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
        }
        .page-content {
            padding: 32px;
            flex: 1;
        }

        /* CARDS */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--gold);
        }
        .stat-card.success::before { background: var(--success); }
        .stat-card.info::before { background: var(--info); }
        .stat-card.warning::before { background: var(--warning); }
        .stat-card.danger::before { background: var(--danger); }
        .stat-label { font-size: 0.72rem; color: var(--muted); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }
        .stat-value { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--cream); }
        .stat-sub { font-size: 0.75rem; color: var(--muted); margin-top: 4px; }
        .stat-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 2.5rem; color: var(--border); }

        /* TABLES */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        thead th {
            text-align: left;
            padding: 10px 14px;
            font-size: 0.68rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
        }
        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        tbody tr:hover { background: var(--surface2); }
        tbody td { padding: 13px 14px; color: var(--text); }
        tbody tr:last-child { border-bottom: none; }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 0.85rem;
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
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { opacity: 0.85; }
        .btn-success { background: var(--success); color: #fff; }
        .btn-success:hover { opacity: 0.85; }
        .btn-sm { padding: 6px 12px; font-size: 0.78rem; }

        /* FORMS */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 0.78rem; color: var(--muted); letter-spacing: 1px; margin-bottom: 6px; text-transform: uppercase; }
        .form-control {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-family: 'DM Sans', sans-serif;
            transition: border-color 0.15s;
        }
        .form-control:focus { outline: none; border-color: var(--gold); }
        select.form-control option { background: var(--surface2); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

        /* STATUS BADGES */
        .status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .status-pending { background: rgba(243,156,18,0.15); color: var(--warning); }
        .status-processing { background: rgba(41,128,185,0.15); color: var(--info); }
        .status-completed { background: rgba(39,174,96,0.15); color: var(--success); }
        .status-cancelled { background: rgba(192,57,43,0.15); color: var(--danger); }
        .status-available { background: rgba(39,174,96,0.15); color: var(--success); }
        .status-occupied { background: rgba(192,57,43,0.15); color: var(--danger); }
        .status-reserved { background: rgba(41,128,185,0.15); color: var(--info); }
        .status-low { background: rgba(243,156,18,0.15); color: var(--warning); }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: var(--cream);
        }
        .page-header p { color: var(--muted); font-size: 0.85rem; margin-top: 2px; }

        /* ALERTS */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: rgba(39,174,96,0.12); border: 1px solid rgba(39,174,96,0.3); color: #5dba82; }
        .alert-error { background: rgba(192,57,43,0.12); border: 1px solid rgba(192,57,43,0.3); color: #e07060; }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            width: 90%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            padding: 22px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--cream); }
        .modal-close { background: none; border: none; color: var(--muted); cursor: pointer; font-size: 1.1rem; }
        .modal-body { padding: 24px; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border); display: flex; gap: 10px; justify-content: flex-end; }

        /* TABS */
        .tabs { display: flex; gap: 4px; margin-bottom: 24px; border-bottom: 1px solid var(--border); }
        .tab-btn {
            padding: 10px 18px;
            background: none;
            border: none;
            color: var(--muted);
            font-size: 0.85rem;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: all 0.15s;
        }
        .tab-btn.active { color: var(--gold); border-bottom-color: var(--gold); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* QR Code area */
        .qr-box {
            background: #fff;
            padding: 16px;
            border-radius: 8px;
            display: inline-block;
        }

        /* SECTION CARD HEADER (used by the section-card component) */
        .section-card-head {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 18px;
        }
        .section-card-title {
            font-family: 'Playfair Display', serif;
            color: var(--cream);
            font-size: 1rem;
            font-weight: 600;
        }

        /* PAGE HEADER ACTIONS (used by the page-header component) */
        .page-header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        /* ROLE BADGE (topbar) */
        .role-badge { padding: 3px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
        .role-badge.role-admin   { background: rgba(201,168,76,0.18); color: var(--gold); }
        .role-badge.role-kasir   { background: rgba(41,128,185,0.18); color: #5fa8d3; }
        .role-badge.role-chef    { background: rgba(192,57,43,0.18);  color: #e07060; }

        /* KITCHEN TICKETS (used by the order-ticket component) */
        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }
        .ticket {
            background: var(--surface);
            border: 1px solid var(--border);
            border-left: 4px solid var(--gold);
            border-radius: 12px;
            padding: 16px 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ticket.ticket-pending    { border-left-color: var(--warning); }
        .ticket.ticket-processing { border-left-color: var(--info); animation: pulseTicket 2s ease-in-out infinite; }
        .ticket.ticket-completed  { border-left-color: var(--success); opacity: 0.75; }
        .ticket.ticket-cancelled  { border-left-color: var(--danger); opacity: 0.55; }
        @keyframes pulseTicket {
            0%,100% { box-shadow: 0 0 0 0 rgba(41,128,185,0); }
            50%     { box-shadow: 0 0 0 4px rgba(41,128,185,0.15); }
        }
        .ticket-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
        .ticket-id { font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--cream); }
        .ticket-meta { color: var(--muted); font-size: 0.78rem; margin-top: 2px; }
        .ticket-items { list-style: none; padding: 8px 0; border-top: 1px dashed var(--border); border-bottom: 1px dashed var(--border); }
        .ticket-items li { display: flex; gap: 10px; padding: 4px 0; font-size: 0.9rem; }
        .ticket-qty { color: var(--gold); font-weight: 600; min-width: 32px; }
        .ticket-name { color: var(--text); }
        .ticket-notes { font-size: 0.8rem; color: var(--warning); background: rgba(243,156,18,0.08); padding: 8px 10px; border-radius: 6px; }
        .ticket-actions { display: flex; gap: 8px; flex-wrap: wrap; }

        /* TABLE-MAP TILES (used by Admin dashboard) */
        .table-map {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 14px;
        }
        .table-tile {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 12px;
            text-align: center;
            transition: transform 0.15s, border-color 0.15s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .table-tile:hover { transform: translateY(-2px); border-color: var(--gold); }
        .table-tile.t-available { border-color: rgba(39,174,96,0.45); }
        .table-tile.t-occupied  { border-color: rgba(192,57,43,0.55); }
        .table-tile.t-reserved  { border-color: rgba(41,128,185,0.55); }
        .table-tile-icon { font-size: 1.6rem; margin-bottom: 4px; }
        .table-tile-name { font-size: 0.9rem; font-weight: 600; color: var(--cream); }
        .table-tile-cap  { font-size: 0.7rem; color: var(--muted); }

        /* MOBILE TOGGLE */
        .mobile-toggle {
            display: none;
            background: none; border: none; color: var(--cream);
            font-size: 1.3rem; cursor: pointer;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }
            .sidebar.is-open { transform: translateX(0); box-shadow: 0 0 40px rgba(0,0,0,0.6); }
            .main { margin-left: 0; }
            .mobile-toggle { display: inline-flex; }
            .page-content { padding: 18px; }
            .topbar { padding: 14px 18px; }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat-card { padding: 14px; }
            .stat-value { font-size: 1.4rem; }
            .ticket-grid { grid-template-columns: 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php $role = Auth::user()->role ?? 'Admin'; @endphp

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="brand">RestoPOS</div>
        <div class="sub">{{ strtoupper($role) }} CONSOLE</div>
    </div>

    <x-sidebar-nav />

    <div class="sidebar-footer">
        <div class="user-name">{{ Auth::user()->name ?? 'Admin' }}</div>
        <div>{{ $role }}</div>
        <a href="{{ route('logout') }}" class="logout-btn"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i> Sign Out
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div style="display:flex; align-items:center; gap:12px;">
            <button class="mobile-toggle" type="button" onclick="document.getElementById('sidebar').classList.toggle('is-open')" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        </div>
        <div class="topbar-right">
            <span><i class="fas fa-clock"></i> {{ now()->format('D, d M Y') }}</span>
            <span class="role-badge role-{{ strtolower($role) }}">{{ $role }}</span>
        </div>
    </div>

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</main>

@stack('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>
