<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Menu;
use App\Models\Table;
use App\Models\CategoryMenu;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller {
    public function show($tableId) {
        $table      = Table::findOrFail($tableId);
        $menus      = Menu::with('category')->where('availability',1)->get();
        $categories = CategoryMenu::all();
        return view('customer.order', compact('table','menus','categories'));
    }

    public function store(Request $req) {
        $req->validate(['table_id'=>'required','items'=>'required|array']);
        $subtotal = collect($req->items)->sum('subtotal');
        $total    = round($subtotal * 1.11);
        $order    = Order::create([
            'order_date'=>now(),'total_amount'=>$total,
            'users_id'=>1,'users_roles_id'=>1,
            'table_id'=>$req->table_id,'order_type'=>'dine-in',
            'payment_type'=>'cash','customer_name'=>'QR Order',
            'notes'=>$req->notes,'status'=>'pending',
        ]);
        foreach ($req->items as $item) {
            OrderDetail::create(['orders_id'=>$order->id,'menus_id'=>$item['menu_id'],'quantity'=>$item['quantity'],'price'=>$item['price'],'subtotal'=>$item['subtotal']]);
        }
        Table::where('id',$req->table_id)->update(['status'=>'occupied']);
        return response()->json(['success'=>true,'order_id'=>$order->id]);
    }
}
