<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::all();
        $floors = Floor::orderBy('id')->get();

        return view('tables.index', compact('tables', 'floors'));
    }

    public function storeFloor(Request $req)
    {
        $req->validate(['name' => 'required|string|max:50']);
        Floor::create(['name' => $req->name]);

        return back()->with('success', 'Floor added!');
    }

    public function destroyFloor($id)
    {
        $floor = Floor::findOrFail($id);
        if ($floor->tables()->exists()) {
            return back()->with('error', 'Cannot delete "'.$floor->name.'" — it still has tables placed on it. Move them first.');
        }
        if (Floor::count() <= 1) {
            return back()->with('error', 'You must keep at least one floor.');
        }
        $floor->delete();

        return back()->with('success', 'Floor deleted!');
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

    public function saveLayout(Request $req)
    {
        $data = $req->validate([
            'positions' => 'present|array',
            'positions.*.id' => 'required|exists:tables,id',
            'positions.*.pos_x' => 'nullable|integer|min:0|max:11',
            'positions.*.pos_y' => 'nullable|integer|min:0',
            'positions.*.floor_id' => 'nullable|exists:floors,id',
        ]);

        // Normalise: a table is "placed" only when it has a floor AND both coords.
        // Otherwise it goes back to the tray (all three nulled).
        $rows = [];
        foreach ($data['positions'] as $p) {
            $floor = $p['floor_id'] ?? null;
            $x = $p['pos_x'] ?? null;
            $y = $p['pos_y'] ?? null;
            $placed = $floor !== null && $x !== null && $y !== null;
            $rows[] = [
                'id' => $p['id'],
                'floor_id' => $placed ? $floor : null,
                'pos_x' => $placed ? $x : null,
                'pos_y' => $placed ? $y : null,
            ];
        }

        // One table per cell *per floor*: reject duplicate (floor,x,y) triples.
        $seen = [];
        foreach ($rows as $r) {
            if ($r['floor_id'] === null) {
                continue;
            }
            $cell = $r['floor_id'].':'.$r['pos_x'].','.$r['pos_y'];
            if (isset($seen[$cell])) {
                throw ValidationException::withMessages([
                    'positions' => 'Two tables share the same cell on one floor — please spread them out.',
                ]);
            }
            $seen[$cell] = true;
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $r) {
                Table::where('id', $r['id'])->update([
                    'floor_id' => $r['floor_id'],
                    'pos_x' => $r['pos_x'],
                    'pos_y' => $r['pos_y'],
                ]);
            }
        });

        return back()->with('success', 'Layout saved!');
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
