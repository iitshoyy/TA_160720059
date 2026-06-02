<?php

namespace App\Http\Controllers;

use App\Models\CategoryMenu;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $req)
    {
        $orders = Order::with(['table', 'orderDetails', 'customer'])
            ->when($req->search, fn ($q) => $q->where('id', 'like', "%{$req->search}%")->orWhere('customer_name', 'like', "%{$req->search}%"))
            ->when($req->status, fn ($q) => $q->where('status', $req->status))
            ->when($req->type, fn ($q) => $q->where('order_type', $req->type))
            ->when($req->date, fn ($q) => $q->whereDate('order_date', $req->date))
            ->latest('order_date')
            ->paginate(20);

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $menus = Menu::with(['category', 'components.ingridient.inventories'])->get();
        $categories = CategoryMenu::all();
        $tables = Table::whereIn('status', ['available'])->get();

        return view('orders.create', compact('menus', 'categories', 'tables'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'order_type' => 'required|in:dine-in,takeaway,pre-order',
            'items' => 'required|json',
            'payment_type' => 'required|in:Cash,Card,QRIS,Transfer',
            'table_id' => 'nullable|exists:tables,id',
            'amount_paid' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
        ]);

        $items = json_decode($data['items'], true);
        if (! is_array($items) || count($items) === 0) {
            return back()->withErrors(['items' => 'Please add at least one item to the order.'])->withInput();
        }
        foreach ($items as $item) {
            if (! isset($item['menu_id'], $item['quantity'], $item['price'], $item['subtotal'])) {
                return back()->withErrors(['items' => 'Invalid cart payload.'])->withInput();
            }
        }

        foreach ($items as $item) {
            $menu = Menu::find($item['menu_id']);
            if (! $menu) {
                return back()->withErrors(['items' => 'A selected menu item no longer exists.'])->withInput();
            }
            $cap = $menu->stockCapacity();
            if ((int) $item['quantity'] > $cap) {
                $msg = $cap === 0
                    ? $menu->name.' is sold out.'
                    : 'Only '.$cap.' portion(s) of '.$menu->name.' available.';
                return back()->withErrors(['items' => $msg])->withInput();
            }
        }

        $subtotal = collect($items)->sum('subtotal');
        $tax = $subtotal * 0.11;
        $total = round($subtotal + $tax);

        if ($data['order_type'] === 'dine-in' && empty($data['table_id'])) {
            return back()->withErrors(['table_id' => 'Pick a table for dine-in orders.'])->withInput();
        }

        try {
            $order = DB::transaction(function () use ($data, $items, $total) {
                $order = Order::create([
                    'order_date' => now(),
                    'total_amount' => $total,
                    'users_id' => Auth::id(),
                    'users_roles_id' => Auth::user()->roles_id,
                    'table_id' => $data['table_id'] ?? null,
                    'order_type' => $data['order_type'],
                    'payment_type' => $data['payment_type'],
                    'payment_date' => now(),
                    'amount_paid' => $data['amount_paid'] ?? null,
                    'customer_name' => $data['customer_name'] ?? 'Walk-in',
                    'status' => 'pending',
                ]);

                foreach ($items as $item) {
                    OrderDetail::create([
                        'orders_id' => $order->id,
                        'menus_id' => $item['menu_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                if (! empty($data['table_id'])) {
                    Table::where('id', $data['table_id'])->update(['status' => 'occupied']);
                }

                // POS path: payment is collected upfront, so inventory is deducted immediately at order creation.
                $this->deductInventory($items);

                return $order;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['items' => 'Stock changed while placing the order. Please review and try again.'])->withInput();
        }

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
            'amount_paid' => 'required|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($id, $data) {
                $order = Order::with('orderDetails')->lockForUpdate()->findOrFail($id);

                if ($order->status !== 'pending' || $order->payment_date !== null) {
                    return back()->with('error', 'This order is not awaiting payment (it may already be paid).');
                }

                if ((float) $data['amount_paid'] < (float) $order->total_amount) {
                    return back()->withErrors(['amount_paid' => 'Amount received is less than the order total.'])->withInput();
                }

                $order->update([
                    'payment_type' => $data['payment_type'],
                    'amount_paid' => $data['amount_paid'],
                    'payment_date' => now(),
                    'users_id' => Auth::id(),
                    'users_roles_id' => Auth::user()->roles_id,
                    'status' => 'processing',
                ]);

                $this->deductInventory(
                    $order->orderDetails->map(fn ($d) => ['menu_id' => $d->menus_id, 'quantity' => (int) $d->quantity])->all()
                );

                $change = (float) $data['amount_paid'] - (float) $order->total_amount;

                return back()->with('success', 'Payment received. Change due: Rp '.number_format($change).'. Order is now processing.');
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Stock changed — unable to complete payment. Please review the order.');
        }
    }

    public function receipt($id)
    {
        $order = Order::with(['orderDetails.menu', 'table'])->findOrFail($id);

        return view('orders.receipt', compact('order'));
    }

    private function deductInventory(array $lines): void
    {
        foreach ($lines as $line) {
            $menu = Menu::with('components.ingridient')->find($line['menu_id']);
            if (! $menu) {
                continue;
            }
            $qty = (int) $line['quantity'];
            foreach ($menu->components as $comp) {
                $needed = (float) $comp->quantity * $qty;
                $inv = $comp->ingridient?->inventories()->lockForUpdate()->first();
                if (! $inv || (float) $inv->quantity_on_hand < $needed) {
                    throw new \RuntimeException('Insufficient stock for '.$menu->name.'.');
                }
                $inv->update([
                    'quantity_on_hand' => (float) $inv->quantity_on_hand - $needed,
                    'last_updated' => now(),
                ]);
            }
        }
    }
}
