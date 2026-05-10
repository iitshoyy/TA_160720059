<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\CategoryMenu;
use Illuminate\Http\Request;

class MenuController extends Controller {
    public function index(Request $req) {
        $menus = Menu::with('category')
            ->when($req->search, fn($q)=>$q->where('name','like',"%{$req->search}%"))
            ->when($req->category, fn($q)=>$q->where('categoryMenus_id',$req->category))
            ->paginate(20);
        $categories = CategoryMenu::all();
        return view('menus.index', compact('menus','categories'));
    }

    public function store(Request $req) {
        $req->validate(['name'=>'required','price'=>'required|numeric','categoryMenus_id'=>'required']);
        Menu::create($req->only(['name','description','price','categoryMenus_id']) + ['availability'=>1]);
        return back()->with('success','Menu item added!');
    }

    public function update(Request $req, $id) {
        Menu::findOrFail($id)->update($req->only(['name','description','price','categoryMenus_id','availability']));
        return back()->with('success','Menu item updated!');
    }

    public function destroy($id) {
        Menu::findOrFail($id)->delete();
        return back()->with('success','Menu item deleted!');
    }

    public function toggleAvailability($id) {
        $m = Menu::findOrFail($id);
        $m->update(['availability' => $m->availability ? 0 : 1]);
        return back()->with('success','Availability updated!');
    }
}
