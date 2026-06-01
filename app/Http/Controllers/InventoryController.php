<?php

namespace App\Http\Controllers;

use App\Models\Ingridient;
use App\Models\IngridientType;
use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller {
    public function index() {
        $ingredients      = Ingridient::with(['type','inventories','supplier'])->paginate(30);
        $ingredientTypes  = IngridientType::all();
        $suppliers        = Supplier::all();
        $totalIngredients = Ingridient::count();
        $lowStock         = Ingridient::whereHas('inventories', fn($q)=>$q->whereRaw('quantity_on_hand <= ingridients.min_stock')->where('quantity_on_hand','>',0))->count();
        $outOfStock       = Ingridient::whereHas('inventories', fn($q)=>$q->where('quantity_on_hand','<=',0))->orWhereDoesntHave('inventories')->count();
        $sufficientStock  = max(0, $totalIngredients - $lowStock - $outOfStock);
        return view('inventory.index', compact('ingredients','ingredientTypes','suppliers','totalIngredients','lowStock','outOfStock','sufficientStock'));
    }

    public function store(Request $req) {
        $req->validate(['name'=>'required','ingridient_types_id'=>'required']);
        $ingredient = Ingridient::create($req->only(['name','unit','cost_per_unit','min_stock','ingridient_types_id','supplier_id']));
        Inventory::create(['ingridient_id'=>$ingredient->id,'quantity_on_hand'=>$req->initial_stock??0,'last_updated'=>now()]);
        return back()->with('success','Ingredient added!');
    }

    public function adjust(Request $req) {
        $req->validate(['ingredient_id'=>'required','quantity'=>'required|numeric','adjustment_type'=>'required']);
        $inv = Inventory::firstOrCreate(['ingridient_id'=>$req->ingredient_id],['quantity_on_hand'=>0,'last_updated'=>now()]);
        match($req->adjustment_type) {
            'add'      => $inv->update(['quantity_on_hand'=>$inv->quantity_on_hand + $req->quantity]),
            'subtract' => $inv->update(['quantity_on_hand'=>max(0,$inv->quantity_on_hand - $req->quantity)]),
            'set'      => $inv->update(['quantity_on_hand'=>$req->quantity]),
            default    => null,
        };
        $inv->update(['last_updated'=>now()]);
        return back()->with('success','Stock adjusted!');
    }

    public function destroy($id) {
        $ing = Ingridient::findOrFail($id);
        if ($ing->components()->exists()) {
            return back()->with('error', 'Cannot delete "'.$ing->name.'" — it is used in a recipe. Remove it from menus first.');
        }
        DB::transaction(function () use ($ing) {
            $ing->inventories()->delete();
            $ing->delete();
        });
        return back()->with('success','Ingredient deleted!');
    }
}
