<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Ingridient;
use App\Models\Table;
use Carbon\Carbon;

/**
 * Admin sees the full operational picture: revenue, orders, reservations,
 * stock health and table state. This is the only role that gets aggregate KPIs.
 */
class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();

        $todayRevenue      = Order::whereDate('order_date', $today)->where('status', 'completed')->sum('total_amount');
        $todayOrders       = Order::whereDate('order_date', $today)->count();
        $pendingOrders     = Order::where('status', 'pending')->count();
        $todayReservations = Reservation::whereDate('reservation_date', $today)->count();

        $lowStock = Ingridient::whereHas('inventories', function ($q) {
            $q->whereRaw('quantity_on_hand <= ingridients.min_stock');
        })->count();

        $weekRevenue = Order::whereBetween('order_date', [Carbon::now()->subDays(6), Carbon::now()])
            ->where('status', 'completed')->sum('total_amount');

        $recentOrders = Order::with(['table', 'orderDetails'])->latest()->take(8)->get();
        $reservations = Reservation::whereDate('reservation_date', $today)->orderBy('reservation_time')->take(8)->get();
        $tables       = Table::orderBy('name')->get();

        return view('dashboards.admin', compact(
            'todayRevenue', 'todayOrders', 'pendingOrders', 'todayReservations',
            'lowStock', 'weekRevenue', 'recentOrders', 'reservations', 'tables'
        ));
    }
}
