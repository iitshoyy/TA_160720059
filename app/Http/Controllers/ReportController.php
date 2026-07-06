<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $req)
    {
        $period = $req->period ?? 'today';
        [$from,$to] = match ($period) {
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'custom' => [Carbon::parse($req->from ?? today()), Carbon::parse($req->to ?? today())->endOfDay()],
            default => [Carbon::today(), Carbon::today()->endOfDay()],
        };

        $base = Order::where('status', 'completed')->whereBetween('order_date', [$from, $to]);
        $rev = (clone $base)->sum('total_amount');
        $cnt = (clone $base)->count();
        $tax = $rev * 0.11 / 1.11;

        // HPP (Harga Pokok Penjualan / COGS): recipe cost per menu unit,
        // derived from each menu's components × ingredient cost_per_unit.
        $menuCost = $this->menuUnitCosts();

        $soldQty = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->where('orders.status', 'completed')->whereBetween('orders.order_date', [$from, $to])
            ->selectRaw('order_details.menus_id as menu_id, SUM(order_details.quantity) as qty')
            ->groupBy('order_details.menus_id')->get();

        $cogs = $soldQty->sum(fn ($r) => ($menuCost[$r->menu_id] ?? 0) * (float) $r->qty);
        $netSales = $rev - $tax;
        $grossProfit = $netSales - $cogs;

        $summary = [
            'revenue' => $rev,
            'orders' => $cnt,
            'avg_order' => $cnt ? $rev / $cnt : 0,
            'tax' => $tax,
            'cogs' => $cogs,
            'net_sales' => $netSales,
            'gross_profit' => $grossProfit,
            'margin' => $netSales ? $grossProfit / $netSales * 100 : 0,
        ];

        $topItems = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->join('menus', 'order_details.menus_id', '=', 'menus.id')
            ->where('orders.status', 'completed')->whereBetween('orders.order_date', [$from, $to])
            ->selectRaw('menus.id as menu_id, menus.name as menu_name, SUM(order_details.quantity) as total_qty, SUM(order_details.subtotal) as total_revenue')
            ->groupBy('menus.id', 'menus.name')->orderByDesc('total_qty')->take(10)->get();
        $topItems->each(function ($item) use ($menuCost) {
            $item->total_cost = ($menuCost[$item->menu_id] ?? 0) * (float) $item->total_qty;
            $item->total_profit = (float) $item->total_revenue - $item->total_cost;
        });

        $paymentBreakdown = (clone $base)->selectRaw('payment_type, COUNT(*) as count, SUM(total_amount) as total')->groupBy('payment_type')->get();

        $dailySales = (clone $base)->selectRaw('DATE(order_date) as date, SUM(total_amount) as revenue')->groupBy('date')->orderBy('date')->get();
        $transactions = (clone $base)->with(['orderDetails', 'table'])->latest()->paginate(20);

        return view('reports.index', compact('summary', 'topItems', 'paymentBreakdown', 'dailySales', 'transactions', 'menuCost'));
    }

    /**
     * Recipe cost (HPP) per single unit of each menu, keyed by menu id.
     */
    private function menuUnitCosts()
    {
        return DB::table('components')
            ->join('ingridients', 'components.ingridients_id', '=', 'ingridients.id')
            ->selectRaw('components.menus_id as menu_id, SUM(components.quantity * ingridients.cost_per_unit) as unit_cost')
            ->groupBy('components.menus_id')
            ->pluck('unit_cost', 'menu_id');
    }

    public function export(Request $req)
    {
        $period = $req->period ?? 'today';
        [$from, $to] = match ($period) {
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'custom' => [Carbon::parse($req->from ?? today()), Carbon::parse($req->to ?? today())->endOfDay()],
            default => [Carbon::today(), Carbon::today()->endOfDay()],
        };

        $filename = 'sales-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';
        $menuCost = $this->menuUnitCosts();

        return response()->streamDownload(function () use ($from, $to, $menuCost) {
            $out = fopen('php://output', 'w');
            // BOM so Excel reads Rp / accents correctly.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Order #', 'Date', 'Customer', 'Type', 'Items', 'Subtotal', 'Tax (11%)', 'Total', 'HPP', 'Gross Profit', 'Payment', 'Status']);
            Order::with('orderDetails')
                ->where('status', 'completed')
                ->whereBetween('order_date', [$from, $to])
                ->orderBy('order_date')
                ->chunk(200, function ($orders) use ($out, $menuCost) {
                    foreach ($orders as $o) {
                        $subtotal = $o->orderDetails->sum('subtotal');
                        $tax = round($subtotal * 0.11);
                        $hpp = round($o->orderDetails->sum(fn ($d) => ($menuCost[$d->menus_id] ?? 0) * (float) $d->quantity));
                        fputcsv($out, [
                            '#'.str_pad($o->id, 4, '0', STR_PAD_LEFT),
                            Carbon::parse($o->order_date)->format('Y-m-d H:i'),
                            $o->customer_name ?: 'Walk-in',
                            ucfirst(str_replace('-', ' ', $o->order_type)),
                            $o->orderDetails->sum('quantity'),
                            $subtotal,
                            $tax,
                            $o->total_amount,
                            $hpp,
                            $subtotal - $hpp,
                            $o->payment_type ?: '-',
                            ucfirst($o->status),
                        ]);
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
