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
        Supplier::findOrFail($id)->delete();
        return back()->with('success','Supplier deleted!');
    }
}
