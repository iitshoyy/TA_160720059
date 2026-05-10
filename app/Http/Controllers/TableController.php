<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller {
    public function index() {
        $tables = Table::all();
        return view('tables.index', compact('tables'));
    }

    public function store(Request $req) {
        $req->validate(['name'=>'required','capacity'=>'required|integer|min:1']);
        Table::create($req->only(['name','capacity']) + ['status'=>'available']);
        return back()->with('success','Table added!');
    }

    public function update(Request $req, $id) {
        Table::findOrFail($id)->update($req->only(['name','capacity','status']));
        return back()->with('success','Table updated!');
    }

    public function destroy($id) {
        Table::findOrFail($id)->delete();
        return back()->with('success','Table deleted!');
    }

    public function generateQR($id) {
        $table = Table::findOrFail($id);
        $url   = route('customer.order', ['tableId' => $id]);
        return view('tables.qr', compact('table','url'));
    }
}
