<?php

namespace App\Http\Controllers;

use App\Models\Ingridient;
use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.ingridient'])->latest()->paginate(20);
        $suppliers = Supplier::all();
        $ingredients = Ingridient::all();

        return view('purchase-orders.index', compact('purchaseOrders', 'suppliers', 'ingredients'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingridients,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
        $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price']);

        DB::transaction(function () use ($data, $total) {
            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'order_date' => now(),
                'expected_date' => $data['expected_date'] ?? null,
                'total_amount' => $total,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);
            foreach ($data['items'] as $item) {
                $po->items()->create([
                    'ingridient_id' => $item['ingredient_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        });

        return back()->with('success', 'Purchase order created!');
    }

    public function show($id)
    {
        $po = PurchaseOrder::with(['supplier', 'items.ingridient'])->findOrFail($id);

        return view('purchase-orders.show', compact('po'));
    }

    public function markReceived(Request $req, $id)
    {
        $data = $req->validate([
            'received' => 'nullable|array',
            'received.*' => 'numeric|min:0',
        ]);
        $received = $data['received'] ?? [];

        DB::transaction(function () use ($id, $received) {
            $po = PurchaseOrder::with('items')->lockForUpdate()->findOrFail($id);
            if ($po->status === 'received') {
                return; // idempotent — don't double-credit stock if button is double-clicked
            }
            $po->update(['status' => 'received']);
            foreach ($po->items as $item) {
                // Confirmed quantity from the receive form; falls back to ordered qty.
                $qty = array_key_exists($item->id, $received) ? (float) $received[$item->id] : (float) $item->quantity;
                $item->update(['received_quantity' => $qty]);

                $inv = Inventory::firstOrCreate(
                    ['ingridient_id' => $item->ingridient_id],
                    ['quantity_on_hand' => 0, 'last_updated' => now()]
                );
                $inv->update([
                    'quantity_on_hand' => $inv->quantity_on_hand + $qty,
                    'last_updated' => now(),
                ]);
            }
        });

        return back()->with('success', 'Purchase order received and stock updated!');
    }

    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status === 'received') {
            return back()->with('error', 'Cannot delete a received purchase order — stock has already been credited.');
        }
        DB::transaction(function () use ($po) {
            $po->items()->delete();
            $po->delete();
        });

        return back()->with('success', 'Purchase order deleted!');
    }
}
