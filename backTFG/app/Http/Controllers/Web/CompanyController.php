<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $query = Company::with(['owner', 'departments'])->orderBy('name');

        if ($search) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tax_id', 'like', "%{$search}%")
            );
        }

        $companies = $query->get();
        return view('admin.companies.index', compact('companies', 'search'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('admin.companies.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'tax_id'   => 'required|string|unique:companies',
            'address'  => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        Company::create($data);
        return redirect()->route('admin.companies')->with('success', 'Empresa creada correctamente');
    }

    public function edit(Company $company)
    {
        $users = User::orderBy('name')->get();
        $company->load(['owner', 'departments']);
        return view('admin.companies.edit', compact('company', 'users'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'tax_id'   => 'required|string|unique:companies,tax_id,' . $company->id,
            'address'  => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $data['active'] = $request->boolean('active');
        $company->update($data);
        return redirect()->route('admin.companies')->with('success', 'Empresa actualizada correctamente');
    }

    public function toggleActive(Company $company)
    {
        $active = !$company->active;
        $company->update(['active' => $active]);
        // Cascada: activar/desactivar todos los departamentos de la empresa
        $deptIds = Department::where('company_id', $company->id)->pluck('id');
        Department::whereIn('id', $deptIds)->update(['active' => $active]);
        // Cascada: activar/desactivar todos los empleados de esos departamentos
        User::whereIn('department_id', $deptIds)->update(['active' => $active]);
        $action = $active ? 'activada' : 'desactivada';
        return redirect()->route('admin.companies')->with('success', "Empresa {$action} correctamente");
    }
}
