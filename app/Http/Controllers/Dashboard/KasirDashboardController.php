<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Cashier (Kasir) is task-focused: process payments, see open tabs.
 * No aggregate KPIs beyond their personal shift counters.
 */
class KasirDashboardController extends Controller
{
    public function __invoke()
    {
        $today  = Carbon::today();
        $userId = Auth::id();

        $myTodayCount  = Order::where('users_id', $userId)->whereDate('order_date', $today)->count();
        $myTodayTotal  = Order::where('users_id', $userId)->whereDate('order_date', $today)->where('status', 'completed')->sum('total_amount');
        $awaitingPay   = Order::where('status', 'pending')->whereNull('payment_date')->count();
        $availTables   = Table::where('status', 'available')->count();

        $openOrders = Order::with(['table', 'orderDetails.menu'])
            ->whereIn('status', ['pending', 'processing'])
            ->latest('order_date')
            ->take(10)
            ->get();

        $recentTransactions = Order::with('table')
            ->where('users_id', $userId)
            ->whereDate('order_date', $today)
            ->latest('order_date')
            ->take(8)
            ->get();

        return view('dashboards.kasir', compact(
            'myTodayCount', 'myTodayTotal', 'awaitingPay', 'availTables',
            'openOrders', 'recentTransactions'
        ));
    }
}
