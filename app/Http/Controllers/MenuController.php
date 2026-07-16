<?php

namespace App\Http\Controllers;

use App\Models\CategoryMenu;
use App\Models\Component;
use App\Models\Ingridient;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller {
    public function index(Request $req) {
        $menus = Menu::with(['category', 'components.ingridient.inventories'])
            ->when($req->search, fn($q)=>$q->where('name','like',"%{$req->search}%"))
            ->when($req->category, fn($q)=>$q->where('categoryMenus_id',$req->category))
            ->paginate(20);
        $categories = CategoryMenu::all();
        $ingredients = Ingridient::orderBy('name')->get(['id','name','unit']);
        return view('menus.index', compact('menus','categories','ingredients'));
    }

    public function store(Request $req) {
        $req->validate(['name'=>'required','price'=>'required|numeric','categoryMenus_id'=>'required','image'=>'nullable|image|max:2048']);
        $data = $req->only(['name','description','price','categoryMenus_id']) + ['availability'=>1];
        if ($req->hasFile('image')) {
            $data['image'] = $req->file('image')->store('menus', 'public');
        }
        Menu::create($data);
        return back()->with('success','Menu item added!');
    }

    public function update(Request $req, $id) {
        $req->validate(['name'=>'required','price'=>'required|numeric','categoryMenus_id'=>'required','image'=>'nullable|image|max:2048']);
        $menu = Menu::findOrFail($id);
        $data = $req->only(['name','description','price','categoryMenus_id']);
        if ($req->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $data['image'] = $req->file('image')->store('menus', 'public');
        }
        $menu->update($data);
        return back()->with('success','Menu item updated!');
    }

    public function destroy($id) {
        $menu = Menu::findOrFail($id);
        if ($menu->orderDetails()->exists()) {
            return back()->with('error', 'Cannot delete "'.$menu->name.'" — it appears in past orders.');
        }
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }
        $menu->components()->delete();
        $menu->delete();
        return back()->with('success','Menu item deleted!');
    }

    /** Return a menu's recipe as JSON for the editor modal. */
    public function recipe($id) {
        return response()->json(
            Menu::findOrFail($id)->components()->get(['ingridients_id','quantity'])
        );
    }

    /** Replace a menu's recipe with the submitted ingredient/quantity pairs. */
    public function updateRecipe(Request $req, $id) {
        $menu = Menu::findOrFail($id);
        $data = $req->validate([
            'ingredients'   => 'array',
            'ingredients.*' => 'required|exists:ingridients,id',
            'quantities'    => 'array',
            'quantities.*'  => 'required|numeric|min:0.0001',
        ]);
        $ingredients = $data['ingredients'] ?? [];
        $quantities  = $data['quantities'] ?? [];

        DB::transaction(function () use ($menu, $ingredients, $quantities) {
            $menu->components()->delete();
            $seen = [];
            foreach ($ingredients as $i => $ingId) {
                if (isset($seen[$ingId]) || ! isset($quantities[$i])) {
                    continue;
                }
                $seen[$ingId] = true;
                Component::create([
                    'menus_id'       => $menu->id,
                    'ingridients_id' => $ingId,
                    'quantity'       => $quantities[$i],
                ]);
            }
        });

        return back()->with('success','Recipe updated!');
    }
}
