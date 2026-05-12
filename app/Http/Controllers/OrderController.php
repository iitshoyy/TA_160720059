<?php

namespace App\Http\Controllers;

use App\Models\CategoryMenu;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $req)
    {
        $orders = Order::with(['table', 'orderDetails', 'customer'])
            ->when($req->search, fn ($q) => $q->where('id', 'like', "%{$req->search}%")->orWhere('customer_name', 'like', "%{$req->search}%"))
            ->when($req->status,  fn ($q) => $q->where('status', $req->status))
            ->when($req->type,    fn ($q) => $q->where('order_type', $req->type))
            ->when($req->date,    fn ($q) => $q->whereDate('order_date', $req->date))
            ->latest('order_date')
            ->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $menus      = Menu::with('category')->where('availability', 1)->get();
        $categories = CategoryMenu::all();
        $tables     = Table::whereIn('status', ['available'])->get();

        return view('orders.create', compact('menus', 'categories', 'tables'));
    }

    public function store(Request $req)
    {
        $req->validate([
            'order_type'   => 'required|in:dine-in,takeaway,pre-order',
            'items'        => 'required|json',
            'payment_type' => 'required',
        ]);

        $items    = json_decode($req->items, true);
        $subtotal = collect($items)->sum('subtotal');
        $tax      = $subtotal * 0.11;
        $total    = $subtotal + $tax;

        $order = Order::create([
            'order_date'     => now(),
            'total_amount'   => round($total),
            'users_id'       => Auth::id(),
            'users_roles_id' => Auth::user()->roles_id,
            'table_id'       => $req->table_id ?: null,
            'order_type'     => $req->order_type,
            'payment_type'   => $req->payment_type,
            'payment_date'   => now(),
            'amount_paid'    => $req->amount_paid,
            'customer_name'  => $req->customer_name ?: 'Walk-in',
            'status'         => 'pending',
        ]);

        foreach ($items as $item) {
            OrderDetail::create([
                'orders_id' => $order->id,
                'menus_id'  => $item['menu_id'],
                'quantity'  => $item['quantity'],
                'price'     => $item['price'],
                'subtotal'  => $item['subtotal'],
            ]);
        }

        if ($req->table_id) {
            Table::where('id', $req->table_id)->update(['status' => 'occupied']);
        }

        $this->deductInventory($items);

        return redirect()->route('orders.receipt', $order->id)->with('success', 'Order placed successfully!');
    }

    public function show($id)
    {
        $order = Order::with(['orderDetails.menu', 'table', 'customer'])->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $req, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $req->status]);

        if (in_array($req->status, ['completed', 'cancelled'], true) && $order->table_id) {
            Table::where('id', $order->table_id)->update(['status' => 'available']);
        }

        return back()->with('success', 'Order status updated!');
    }

    public function collectPayment(Request $req, $id)
    {
        $data = $req->validate([
            'payment_type' => 'required|in:Cash,Card,QRIS,Transfer',
            'amount_paid'  => 'required|numeric|min:0',
        ]);

        $order = Order::with('orderDetails')->findOrFail($id);

        if ($order->status !== 'pending' || $order->payment_date !== null) {
            return back()->with('error', 'This order is not awaiting payment (it may already be paid).');
        }

        if ((float) $data['amount_paid'] < (float) $order->total_amount) {
            return back()->withErrors(['amount_paid' => 'Amount received is less than the order total.'])->withInput();
        }

        $order->update([
            'payment_type'   => $data['payment_type'],
            'amount_paid'    => $data['amount_paid'],
            'payment_date'   => now(),
            'users_id'       => Auth::id(),
            'users_roles_id' => Auth::user()->roles_id,
            'status'         => 'processing',
        ]);

        $this->deductInventory(
            $order->orderDetails->map(fn ($d) => ['menu_id' => $d->menus_id, 'quantity' => (int) $d->quantity])->all()
        );

        $change = (float) $data['amount_paid'] - (float) $order->total_amount;

        return back()->with('success', 'Payment received. Change due: Rp '.number_format($change).'. Order is now processing.');
    }

    public function receipt($id)
    {
        $order = Order::with(['orderDetails.menu', 'table'])->findOrFail($id);

        return view('orders.receipt', compact('order'));
    }

    /**
     * Subtract recipe ingredients from inventory.
     *
     * @param array<int,array{menu_id:mixed,quantity:int|string}> $lines
     */
    private function deductInventory(array $lines): void
    {
        foreach ($lines as $line) {
            $menu = Menu::with('components.ingridient.inventories')->find($line['menu_id']);
            if (! $menu) {
                continue;
            }

            foreach ($menu->components as $comp) {
                $needed = (float) $comp->quantity * (int) $line['quantity'];
                $inv    = $comp->ingridient?->inventories()->first();
                if ($inv) {
                    $inv->update([
                        'quantity_on_hand' => max(0, (float) $inv->quantity_on_hand - $needed),
                        'last_updated'     => now(),
                    ]);
                }
            }
        }
    }
}
