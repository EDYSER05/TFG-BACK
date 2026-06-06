<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'       => User::count(),
            'companies'   => Company::count(),
            'departments' => Department::count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }
}
