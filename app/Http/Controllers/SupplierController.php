<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller {
    public function index() {
        $suppliers = Supplier::withCount('ingridients')->paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $req) {
        $req->validate(['name'=>'required']);
        Supplier::create($req->only(['name','phone','email','address']));
        return back()->with('success','Supplier added!');
    }

    public function update(Request $req, $id) {
        Supplier::findOrFail($id)->update($req->only(['name','phone','email','address']));
        return back()->with('success','Supplier updated!');
    }

    public function destroy($id) {
        $s = Supplier::withCount(['ingridients','purchaseOrders'])->findOrFail($id);
        if ($s->ingridients_count > 0 || $s->purchase_orders_count > 0) {
            return back()->with('error', 'Cannot delete "'.$s->name.'" — it is linked to ingredients or purchase orders.');
        }
        $s->delete();
        return back()->with('success','Supplier deleted!');
    }
}
