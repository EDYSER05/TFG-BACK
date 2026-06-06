<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search        = $request->input('search', '');
        $roleFilter    = $request->input('role', '');
        $companyFilter = $request->input('company_id', '');

        $query = User::with(['role', 'department.company'])->orderBy('name');

        if ($search) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            );
        }

        if ($roleFilter) {
            $query->whereHas('role', fn($q) => $q->where('name', $roleFilter));
        }

        if ($companyFilter) {
            $query->whereHas('department', fn($q) => $q->where('company_id', $companyFilter));
        }

        $users     = $query->get();
        $roles     = Role::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'companies', 'search', 'roleFilter', 'companyFilter'));
    }

    public function create()
    {
        $roles       = Role::all();
        $departments = Department::with('company')->orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:8',
            'role_id'       => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'hire_date'     => 'required|date',
        ]);

        $data['password']             = Hash::make($data['password']);
        $data['must_change_password'] = true;

        User::create($data);
        return redirect()->route('admin.users')->with('success', 'Usuario creado correctamente');
    }

    public function edit(User $user)
    {
        $roles       = Role::all();
        $departments = Department::with('company')->orderBy('name')->get();
        $user->load(['role', 'department']);
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'role_id'       => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'hire_date'     => 'nullable|date',
            'password'      => 'nullable|min:8',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $data['active'] = $request->boolean('active');
        $user->update($data);
        return redirect()->route('admin.users')->with('success', 'Usuario actualizado correctamente');
    }

    public function toggleActive(User $user)
    {
        $active = !$user->active;
        $user->update(['active' => $active]);
        $action = $active ? 'activado' : 'desactivado';
        return redirect()->route('admin.users')->with('success', "Usuario {$action} correctamente");
    }
}
