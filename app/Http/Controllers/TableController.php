<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::all();

        return view('tables.index', compact('tables'));
    }

    public function store(Request $req)
    {
        $req->validate(['name' => 'required', 'capacity' => 'required|integer|min:1']);
        Table::create($req->only(['name', 'capacity']) + ['status' => 'available']);

        return back()->with('success', 'Table added!');
    }

    public function update(Request $req, $id)
    {
        Table::findOrFail($id)->update($req->only(['name', 'capacity', 'status']));

        return back()->with('success', 'Table updated!');
    }

    public function destroy($id)
    {
        $table = Table::findOrFail($id);
        if ($table->orders()->exists() || $table->reservations()->exists()) {
            return back()->with('error', 'Cannot delete "'.$table->name.'" — it has order or reservation history.');
        }
        $table->delete();

        return back()->with('success', 'Table deleted!');
    }

    public function generateQR($id)
    {
        $table = Table::findOrFail($id);
        $url = route('customer.order', ['tableId' => $id]);
        $qr = QrCode::format('svg')->size(220)->margin(1)->generate($url);

        return view('tables.qr', compact('table', 'url', 'qr'));
    }

    public function qrSheet()
    {
        $tables = Table::orderBy('name')->get()->map(function ($t) {
            $t->qr_url = route('customer.order', ['tableId' => $t->id]);
            $t->qr_svg = QrCode::format('svg')->size(150)->margin(1)->generate($t->qr_url);

            return $t;
        });

        return view('tables.qr-sheet', compact('tables'));
    }
}
