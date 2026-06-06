<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\DepartmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    // Autenticación
    Route::get('login', [AuthController::class, 'loginForm'])->name('admin.login');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout')->middleware('ensure.admin');

    Route::middleware('ensure.admin')->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Usuarios
        Route::resource('users', UserController::class)->except(['show', 'destroy'])->names([
            'index'  => 'admin.users',
            'create' => 'admin.users.create',
            'store'  => 'admin.users.store',
            'edit'   => 'admin.users.edit',
            'update' => 'admin.users.update',
        ]);
        Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('admin.users.toggle-active');

        // Empresas
        Route::resource('companies', CompanyController::class)->except(['show', 'destroy'])->names([
            'index'  => 'admin.companies',
            'create' => 'admin.companies.create',
            'store'  => 'admin.companies.store',
            'edit'   => 'admin.companies.edit',
            'update' => 'admin.companies.update',
        ]);
        Route::post('companies/{company}/toggle-active', [CompanyController::class, 'toggleActive'])->name('admin.companies.toggle-active');

        // Departamentos
        Route::resource('departments', DepartmentController::class)->except(['show', 'destroy'])->names([
            'index'  => 'admin.departments',
            'create' => 'admin.departments.create',
            'store'  => 'admin.departments.store',
            'edit'   => 'admin.departments.edit',
            'update' => 'admin.departments.update',
        ]);
        Route::post('departments/{department}/toggle-active', [DepartmentController::class, 'toggleActive'])->name('admin.departments.toggle-active');
    });
});
