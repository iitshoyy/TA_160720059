<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user.role')->latest('id')->paginate(20);
        // Only Kasir and Chef are creatable here — Admin is provisioned by seeding.
        $roles = Role::whereIn('name', ['Kasir', 'Chef'])->orderBy('name')->get();

        return view('employees.index', compact('employees', 'roles'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:45|alpha_dash|unique:users,username',
            'email'     => 'nullable|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'roles_id'  => 'required|exists:roles,id',
            'position'  => 'required|string|max:45',
            'phone'     => 'nullable|string|max:45',
            'hire_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'username' => $data['username'],
                'email'    => $data['email'] ?? $data['username'].'@restopos.local',
                'password' => Hash::make($data['password']),
                'roles_id' => $data['roles_id'],
            ]);
            Employee::create([
                'users_id'  => $user->id,
                'position'  => $data['position'],
                'phone'     => $data['phone'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,
                'status'    => 'active',
            ]);
        });

        return back()->with('success', 'Employee account created!');
    }

    public function update(Request $req, $id)
    {
        $emp = Employee::with('user')->findOrFail($id);

        $data = $req->validate([
            'name'      => 'required|string|max:255',
            'roles_id'  => 'required|exists:roles,id',
            'position'  => 'required|string|max:45',
            'phone'     => 'nullable|string|max:45',
            'hire_date' => 'nullable|date',
            'status'    => 'required|in:active,inactive',
            'password'  => 'nullable|string|min:6',
        ]);

        DB::transaction(function () use ($emp, $data) {
            if ($emp->user) {
                $userUpdate = [
                    'name'     => $data['name'],
                    'roles_id' => $data['roles_id'],
                ];
                if (! empty($data['password'])) {
                    $userUpdate['password'] = Hash::make($data['password']);
                }
                $emp->user->update($userUpdate);
            }
            $emp->update([
                'position'  => $data['position'],
                'phone'     => $data['phone'] ?? null,
                'hire_date' => $data['hire_date'] ?? null,
                'status'    => $data['status'],
            ]);
        });

        return back()->with('success', 'Employee updated!');
    }

    public function destroy($id)
    {
        $emp = Employee::with('user')->findOrFail($id);

        DB::transaction(function () use ($emp) {
            $user = $emp->user;
            $emp->delete();

            // Drop the user account too — but only if it isn't referenced by past orders.
            // Otherwise just leave the user inactive-by-orphan (no employee record).
            if ($user && ! $user->orders()->exists()) {
                $user->delete();
            }
        });

        return back()->with('success', 'Employee removed!');
    }
}
