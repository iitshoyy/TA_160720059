<?php

namespace App\Http\Controllers;

use App\Models\CategoryMenu;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Table;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function show($tableId)
    {
        $table      = Table::findOrFail($tableId);
        $menus      = Menu::with('category')->where('availability', 1)->get();
        $categories = CategoryMenu::all();

        return view('customer.order', compact('table', 'menus', 'categories'));
    }

    public function store(Request $req)
    {
        $req->validate([
            'table_id'           => 'required|exists:tables,id',
            'items'              => 'required|array|min:1',
            'items.*.menu_id'    => 'required|exists:menus,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
            'items.*.subtotal'   => 'required|numeric|min:0',
            'name'               => 'nullable|string|max:255',
            'notes'              => 'nullable|string',
        ]);

        $subtotal = collect($req->items)->sum('subtotal');
        $total    = round($subtotal * 1.11);

        $order = Order::create([
            'order_date'     => now(),
            'total_amount'   => $total,
            'users_id'       => null,
            'users_roles_id' => null,
            'table_id'       => $req->table_id,
            'order_type'     => 'dine-in',
            'customer_name'  => $req->filled('name') ? $req->name : 'QR Order',
            'notes'          => $req->notes,
            'status'         => 'pending',
        ]);

        foreach ($req->items as $item) {
            OrderDetail::create([
                'orders_id' => $order->id,
                'menus_id'  => $item['menu_id'],
                'quantity'  => $item['quantity'],
                'price'     => $item['price'],
                'subtotal'  => $item['subtotal'],
            ]);
        }

        Table::where('id', $req->table_id)->update(['status' => 'occupied']);

        return response()->json([
            'success'    => true,
            'order_id'   => $order->id,
            'status_url' => route('customer.order.status', $order->id),
        ]);
    }

    public function status($id)
    {
        $order = Order::with(['orderDetails.menu', 'table'])->findOrFail($id);

        return view('customer.status', compact('order'));
    }

    public function statusState($id)
    {
        $order = Order::findOrFail($id);

        return response()->json([
            'status'       => $order->status,
            'total_amount' => $order->total_amount,
        ]);
    }
}
