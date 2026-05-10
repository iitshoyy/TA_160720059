<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ingridient;
use App\Models\Menu;

/**
 * Chef view is a kitchen display system (KDS): tickets to cook, ingredient alerts,
 * and quick toggles for menu availability when something runs out.
 */
class ChefDashboardController extends Controller
{
    public function __invoke()
    {
        $pendingTickets    = Order::with(['table', 'orderDetails.menu'])
            ->where('status', 'pending')->orderBy('order_date')->get();
        $processingTickets = Order::with(['table', 'orderDetails.menu'])
            ->where('status', 'processing')->orderBy('order_date')->get();

        $pendingCount    = $pendingTickets->count();
        $processingCount = $processingTickets->count();
        $completedToday  = Order::whereDate('order_date', today())->where('status', 'completed')->count();

        $lowStock = Ingridient::with(['inventories', 'type'])
            ->whereHas('inventories', function ($q) {
                $q->whereRaw('quantity_on_hand <= ingridients.min_stock');
            })->take(8)->get();

        $unavailableMenus = Menu::where('availability', 0)->get();

        return view('dashboards.chef', compact(
            'pendingTickets', 'processingTickets',
            'pendingCount', 'processingCount', 'completedToday',
            'lowStock', 'unavailableMenus'
        ));
    }
}
