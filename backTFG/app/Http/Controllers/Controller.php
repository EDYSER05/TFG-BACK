<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class Controller
{
    // Devuelve los IDs de usuarios que pertenecen al scope indicado por ?department_id o ?company_id.
    // Útil para filtrar fichajes, ausencias, etc. por departamento o empresa.
    protected function userIdsForScope(Request $request): Collection
    {
        if ($request->department_id) {
            return User::where('department_id', $request->department_id)->pluck('id');
        }

        if ($request->company_id) {
            $deptUserIds = User::whereHas('department', fn($q) => $q->where('company_id', $request->company_id))->pluck('id');
            $ownerIds    = Company::where('id', $request->company_id)->pluck('owner_id')->filter();
            return $deptUserIds->merge($ownerIds)->unique()->values();
        }

        return collect();
    }
}
