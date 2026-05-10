<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Ingridient;
use App\Models\Inventory;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller {
    public function index() {
        $purchaseOrders = PurchaseOrder::with(['supplier','items.ingridient'])->latest()->paginate(20);
        $suppliers      = Supplier::all();
        $ingredients    = Ingridient::all();
        return view('purchase-orders.index', compact('purchaseOrders','suppliers','ingredients'));
    }

    public function store(Request $req) {
        $req->validate(['supplier_id'=>'required','items'=>'required|array']);
        $total = collect($req->items)->sum(fn($i)=>($i['quantity']??0)*($i['unit_price']??0));
        $po    = PurchaseOrder::create(['supplier_id'=>$req->supplier_id,'order_date'=>now(),'expected_date'=>$req->expected_date,'total_amount'=>$total,'notes'=>$req->notes,'status'=>'pending']);
        foreach ($req->items as $item) {
            $po->items()->create(['ingridient_id'=>$item['ingredient_id'],'quantity'=>$item['quantity'],'unit_price'=>$item['unit_price'],'subtotal'=>$item['quantity']*$item['unit_price']]);
        }
        return back()->with('success','Purchase order created!');
    }

    public function show($id) {
        $po = PurchaseOrder::with(['supplier','items.ingridient'])->findOrFail($id);
        return view('purchase-orders.show', compact('po'));
    }

    public function markReceived($id) {
        $po = PurchaseOrder::with('items')->findOrFail($id);
        $po->update(['status'=>'received']);
        foreach ($po->items as $item) {
            $inv = Inventory::firstOrCreate(['ingridient_id'=>$item->ingridient_id],['quantity_on_hand'=>0,'last_updated'=>now()]);
            $inv->update(['quantity_on_hand'=>$inv->quantity_on_hand+$item->quantity,'last_updated'=>now()]);
        }
        return back()->with('success','Purchase order received and stock updated!');
    }

    public function destroy($id) {
        PurchaseOrder::findOrFail($id)->delete();
        return back()->with('success','Purchase order deleted!');
    }
}
