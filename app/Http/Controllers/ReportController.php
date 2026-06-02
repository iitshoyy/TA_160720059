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
        $summary = ['revenue' => $rev, 'orders' => $cnt, 'avg_order' => $cnt ? $rev / $cnt : 0, 'tax' => $rev * 0.11 / 1.11];

        $topItems = DB::table('order_details')
            ->join('orders', 'order_details.orders_id', '=', 'orders.id')
            ->join('menus', 'order_details.menus_id', '=', 'menus.id')
            ->where('orders.status', 'completed')->whereBetween('orders.order_date', [$from, $to])
            ->selectRaw('menus.name as menu_name, SUM(order_details.quantity) as total_qty, SUM(order_details.subtotal) as total_revenue')
            ->groupBy('menus.id', 'menus.name')->orderByDesc('total_qty')->take(10)->get();

        $paymentBreakdown = (clone $base)->selectRaw('payment_type, COUNT(*) as count, SUM(total_amount) as total')->groupBy('payment_type')->get();

        $dailySales = (clone $base)->selectRaw('DATE(order_date) as date, SUM(total_amount) as revenue')->groupBy('date')->orderBy('date')->get();
        $transactions = (clone $base)->with(['orderDetails', 'table'])->latest()->paginate(20);

        return view('reports.index', compact('summary', 'topItems', 'paymentBreakdown', 'dailySales', 'transactions'));
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

        return response()->streamDownload(function () use ($from, $to) {
            $out = fopen('php://output', 'w');
            // BOM so Excel reads Rp / accents correctly.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Order #', 'Date', 'Customer', 'Type', 'Items', 'Subtotal', 'Tax (11%)', 'Total', 'Payment', 'Status']);
            Order::with('orderDetails')
                ->where('status', 'completed')
                ->whereBetween('order_date', [$from, $to])
                ->orderBy('order_date')
                ->chunk(200, function ($orders) use ($out) {
                    foreach ($orders as $o) {
                        $subtotal = $o->orderDetails->sum('subtotal');
                        $tax = round($subtotal * 0.11);
                        fputcsv($out, [
                            '#'.str_pad($o->id, 4, '0', STR_PAD_LEFT),
                            Carbon::parse($o->order_date)->format('Y-m-d H:i'),
                            $o->customer_name ?: 'Walk-in',
                            ucfirst(str_replace('-', ' ', $o->order_type)),
                            $o->orderDetails->sum('quantity'),
                            $subtotal,
                            $tax,
                            $o->total_amount,
                            $o->payment_type ?: '-',
                            ucfirst($o->status),
                        ]);
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
