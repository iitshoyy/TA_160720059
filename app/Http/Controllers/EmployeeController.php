<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller {
    public function index() {
        $employees = Employee::with('user')->paginate(20);
        $users     = User::all();
        return view('employees.index', compact('employees','users'));
    }

    public function store(Request $req) {
        $req->validate(['users_id'=>'required','position'=>'required']);
        Employee::create($req->only(['users_id','position','phone','hire_date'])+['status'=>'active']);
        return back()->with('success','Employee added!');
    }

    public function update(Request $req, $id) {
        Employee::findOrFail($id)->update($req->only(['position','phone','hire_date','status']));
        return back()->with('success','Employee updated!');
    }

    public function destroy($id) {
        Employee::findOrFail($id)->delete();
        return back()->with('success','Employee removed!');
    }
}
