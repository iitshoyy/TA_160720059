<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RestoPOS') — Restaurant Management</title>
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
            --sidebar-w: 240px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            font-size: 14px;
            line-height: 1.5;
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
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-logo .brand {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--gold);
        }
        .sidebar-logo .sub {
            font-size: 0.7rem;
            color: var(--muted);
            margin-top: 2px;
        }
        .sidebar-section { padding: 14px 0 0; }
        .sidebar-label {
            font-size: 0.7rem;
            color: var(--muted);
            padding: 0 20px 6px;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 20px;
            color: #8a8070;
            text-decoration: none;
            font-size: 0.88rem;
        }
        .sidebar-nav a:hover { color: var(--cream); }
        .sidebar-nav a.active {
            color: var(--gold);
            border-left: 2px solid var(--gold);
            padding-left: 18px;
        }
        .sidebar-nav a i { width: 16px; text-align: center; font-size: 0.85rem; }
        .sidebar-footer {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            font-size: 0.8rem;
            color: var(--muted);
        }
        .sidebar-footer .user-name { color: var(--cream); margin-bottom: 2px; }
        .sidebar-footer .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            color: var(--danger);
            text-decoration: none;
            font-size: 0.8rem;
        }
        .sidebar-footer .logout-btn:hover { text-decoration: underline; }

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
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .topbar-title { font-size: 1rem; color: var(--cream); font-weight: 600; }
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 0.82rem;
            color: var(--muted);
        }
        .badge-pill {
            background: var(--gold);
            color: #000;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 1px 6px;
        }
        .page-content { padding: 24px; flex: 1; }

        /* CARDS */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 20px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 16px 18px;
        }
        .stat-label { font-size: 0.75rem; color: var(--muted); margin-bottom: 4px; }
        .stat-value { font-size: 1.5rem; color: var(--cream); font-weight: 600; }
        .stat-sub { font-size: 0.75rem; color: var(--muted); margin-top: 4px; }

        /* TABLES */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        thead th {
            text-align: left;
            padding: 8px 12px;
            font-size: 0.75rem;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            font-weight: 600;
        }
        tbody tr { border-bottom: 1px solid var(--border); }
        tbody td { padding: 10px 12px; color: var(--text); }
        tbody tr:last-child { border-bottom: none; }

        /* BUTTONS */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
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
        .btn-danger { background: var(--danger); color: #fff; border-color: var(--danger); }
        .btn-success { background: var(--success); color: #fff; border-color: var(--success); }
        .btn-sm { padding: 4px 10px; font-size: 0.78rem; }

        /* FORMS */
        .form-group { margin-bottom: 14px; }
        .form-label { display: block; font-size: 0.8rem; color: var(--muted); margin-bottom: 4px; }
        .form-control {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 8px 12px;
            font-size: 0.875rem;
            font-family: inherit;
        }
        .form-control:focus { outline: none; border-color: var(--gold); }
        select.form-control option { background: var(--surface2); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }

        /* STATUS BADGES */
        .status {
            display: inline-block;
            padding: 1px 8px;
            font-size: 0.75rem;
            font-weight: 500;
            border: 1px solid currentColor;
        }
        .status-pending    { color: var(--warning); }
        .status-processing { color: var(--info); }
        .status-completed  { color: var(--success); }
        .status-cancelled  { color: var(--danger); }
        .status-available  { color: var(--success); }
        .status-occupied   { color: var(--danger); }
        .status-reserved   { color: var(--info); }
        .status-low        { color: var(--warning); }
        .status-confirmed  { color: var(--success); }
        .status-arrived    { color: var(--success); }
        .status-info       { color: var(--info); }

        /* UTILITIES */
        .text-muted { color: var(--muted); }
        .text-cream { color: var(--cream); }
        .text-gold  { color: var(--gold); }
        .fw-500     { font-weight: 500; }
        .fw-600     { font-weight: 600; }
        .flex-between { display: flex; align-items: center; justify-content: space-between; }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .page-header h1 { font-size: 1.25rem; color: var(--cream); font-weight: 600; }
        .page-header p { color: var(--muted); font-size: 0.85rem; margin-top: 2px; }

        /* ALERTS */
        .alert {
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid currentColor;
        }
        .alert-success { color: var(--success); }
        .alert-error   { color: var(--danger); }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            width: 90%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title { font-size: 1rem; color: var(--cream); font-weight: 600; }
        .modal-close { background: none; border: none; color: var(--muted); cursor: pointer; font-size: 1rem; }
        .modal-body { padding: 20px; }
        .modal-footer { padding: 12px 20px; border-top: 1px solid var(--border); display: flex; gap: 8px; justify-content: flex-end; }

        /* TABS */
        .tabs { display: flex; gap: 4px; margin-bottom: 18px; border-bottom: 1px solid var(--border); }
        .tab-btn {
            padding: 8px 14px;
            background: none;
            border: none;
            color: var(--muted);
            font-size: 0.85rem;
            font-family: inherit;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
        }
        .tab-btn.active { color: var(--gold); border-bottom-color: var(--gold); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* SECTION CARD HEADER */
        .section-card-head {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 14px;
        }
        .section-card-title { color: var(--cream); font-size: 0.95rem; font-weight: 600; }

        /* PAGE HEADER ACTIONS */
        .page-header-actions { display: flex; gap: 8px; flex-wrap: wrap; }

        /* ROLE BADGE (topbar) */
        .role-badge { padding: 1px 8px; font-size: 0.72rem; font-weight: 600; border: 1px solid currentColor; }
        .role-badge.role-admin { color: var(--gold); }
        .role-badge.role-kasir { color: #5fa8d3; }
        .role-badge.role-chef  { color: #e07060; }

        /* KITCHEN TICKETS */
        .ticket-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
        }
        .ticket {
            background: var(--surface);
            border: 1px solid var(--border);
            border-left: 3px solid var(--gold);
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .ticket.ticket-pending    { border-left-color: var(--warning); }
        .ticket.ticket-processing { border-left-color: var(--info); }
        .ticket.ticket-completed  { border-left-color: var(--success); opacity: 0.7; }
        .ticket.ticket-cancelled  { border-left-color: var(--danger); opacity: 0.5; }
        .ticket-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
        .ticket-id { font-size: 1rem; color: var(--cream); font-weight: 600; }
        .ticket-meta { color: var(--muted); font-size: 0.78rem; margin-top: 2px; }
        .ticket-items { list-style: none; padding: 6px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .ticket-items li { display: flex; gap: 10px; padding: 3px 0; font-size: 0.88rem; }
        .ticket-qty { color: var(--gold); font-weight: 600; min-width: 28px; }
        .ticket-name { color: var(--text); }
        .ticket-notes { font-size: 0.8rem; color: var(--warning); padding: 6px 8px; border: 1px solid var(--warning); }
        .ticket-actions { display: flex; gap: 6px; flex-wrap: wrap; }

        /* TABLE-MAP TILES */
        .table-map {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        .table-tile {
            background: var(--surface2);
            border: 1px solid var(--border);
            padding: 14px 10px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .table-tile:hover { border-color: var(--gold); }
        .table-tile.t-available { border-left: 3px solid var(--success); }
        .table-tile.t-occupied  { border-left: 3px solid var(--danger); }
        .table-tile.t-reserved  { border-left: 3px solid var(--info); }
        .table-tile-icon { font-size: 1.4rem; margin-bottom: 4px; }
        .table-tile-name { font-size: 0.9rem; font-weight: 600; color: var(--cream); }
        .table-tile-cap  { font-size: 0.72rem; color: var(--muted); }

        /* MOBILE TOGGLE */
        .mobile-toggle {
            display: none;
            background: none; border: none; color: var(--cream);
            font-size: 1.2rem; cursor: pointer;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.2s ease; }
            .sidebar.is-open { transform: translateX(0); }
            .main { margin-left: 0; }
            .mobile-toggle { display: inline-flex; }
            .page-content { padding: 16px; }
            .topbar { padding: 12px 16px; }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .stat-card { padding: 12px; }
            .stat-value { font-size: 1.2rem; }
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
