@php
    /**
     * Role-aware sidebar navigation.
     *
     * Sections are grouped by domain (not access tier). A section renders
     * only when at least one of its links is allowed for the current role.
     */
    $role = Auth::user()->role ?? 'Admin';

    // Chef's "dashboard" route renders the Kitchen Display, so label it accordingly.
    $dashboardLabel = $role === 'Chef' ? 'Kitchen Display' : 'Dashboard';
    $dashboardIcon  = $role === 'Chef' ? 'fas fa-fire-burner' : 'fas fa-chart-pie';

    $sections = [
        'Overview' => [
            ['label'=>$dashboardLabel,'icon'=>$dashboardIcon,'route'=>'dashboard','active'=>'dashboard','roles'=>['Admin','Kasir','Chef']],
        ],
        'Point of Sale' => [
            ['label'=>'New Order','icon'=>'fas fa-cash-register','route'=>'orders.create','active'=>'orders.create','roles'=>['Admin','Kasir']],
            ['label'=>'Orders',   'icon'=>'fas fa-receipt',      'route'=>'orders.index', 'active'=>'orders.index|orders.show','roles'=>['Admin','Kasir']],
        ],
        'Kitchen' => [
            ['label'=>'Menu Items','icon'=>'fas fa-utensils','route'=>'menus.index',    'active'=>'menus.*',    'roles'=>['Admin','Chef']],
            ['label'=>'Inventory', 'icon'=>'fas fa-boxes',   'route'=>'inventory.index','active'=>'inventory.*','roles'=>['Admin','Chef']],
        ],
        'Floor' => [
            ['label'=>'Tables',      'icon'=>'fas fa-chair',          'route'=>'tables.index',      'active'=>'tables.*',      'roles'=>['Admin','Kasir']],
            ['label'=>'Reservations','icon'=>'fas fa-calendar-check', 'route'=>'reservations.index','active'=>'reservations.*','roles'=>['Admin','Kasir']],
        ],
        'Procurement' => [
            ['label'=>'Suppliers',      'icon'=>'fas fa-truck',        'route'=>'suppliers.index',      'active'=>'suppliers.*',      'roles'=>['Admin']],
            ['label'=>'Purchase Orders','icon'=>'fas fa-file-invoice', 'route'=>'purchase-orders.index','active'=>'purchase-orders.*','roles'=>['Admin']],
        ],
        'Management' => [
            ['label'=>'Employees','icon'=>'fas fa-users',     'route'=>'employees.index','active'=>'employees.*','roles'=>['Admin']],
            ['label'=>'Reports',  'icon'=>'fas fa-chart-bar', 'route'=>'reports.index',  'active'=>'reports.*',  'roles'=>['Admin']],
        ],
    ];
@endphp

@foreach($sections as $sectionName => $links)
    @php $visible = collect($links)->filter(fn($l) => in_array($role, $l['roles'], true)); @endphp
    @if($visible->isNotEmpty())
        <div class="sidebar-section">
            <div class="sidebar-label">{{ $sectionName }}</div>
            <nav class="sidebar-nav">
                @foreach($visible as $link)
                    <a href="{{ route($link['route']) }}"
                       class="{{ request()->routeIs(explode('|', $link['active'])) ? 'active' : '' }}">
                        <i class="{{ $link['icon'] }}"></i> {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif
@endforeach
