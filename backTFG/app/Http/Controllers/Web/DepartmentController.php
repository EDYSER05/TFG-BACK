<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search        = $request->input('search', '');
        $companyFilter = $request->input('company_id', '');

        $query = Department::with(['company', 'manager'])->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($companyFilter) {
            $query->where('company_id', $companyFilter);
        }

        $departments = $query->get();
        $companies   = Company::orderBy('name')->get();

        return view('admin.departments.index', compact('departments', 'companies', 'search', 'companyFilter'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $managers  = User::with('role')
            ->whereHas('role', fn($q) => $q->whereIn('name', ['owner', 'manager', 'hr']))
            ->orderBy('name')
            ->get();
        return view('admin.departments.create', compact('companies', 'managers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        Department::create($data);
        return redirect()->route('admin.departments')->with('success', 'Departamento creado correctamente');
    }

    public function edit(Department $department)
    {
        $department->load(['company', 'manager']);
        $companies = Company::orderBy('name')->get();
        $managers  = User::with('role')
            ->whereHas('role', fn($q) => $q->whereIn('name', ['owner', 'manager', 'hr']))
            ->orderBy('name')
            ->get();
        return view('admin.departments.edit', compact('department', 'companies', 'managers'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $data['active'] = $request->boolean('active');
        $department->update($data);
        return redirect()->route('admin.departments')->with('success', 'Departamento actualizado correctamente');
    }

    public function toggleActive(Department $department)
    {
        $active = !$department->active;
        $department->update(['active' => $active]);
        // Cascada: activar/desactivar todos los empleados del departamento
        User::where('department_id', $department->id)->update(['active' => $active]);
        $action = $active ? 'activado' : 'desactivado';
        return redirect()->route('admin.departments')->with('success', "Departamento {$action} correctamente");
    }
}
