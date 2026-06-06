@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h3 class="text-xl font-semibold text-gray-800">Bienvenido, {{ Auth::user()->name }} {{ Auth::user()->last_name }}</h3>
    <p class="text-gray-500 text-sm mt-0.5">Panel de administración de NexTime</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Usuarios</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['users'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Departamentos</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['departments'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Empresas</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['companies'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <a href="{{ route('admin.users') }}"
        class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-300 hover:shadow-sm transition group">
        <p class="font-semibold text-gray-800 group-hover:text-indigo-600 transition">Gestión de usuarios</p>
        <p class="text-sm text-gray-400 mt-1">Crear, editar y eliminar usuarios del sistema</p>
    </a>
    <a href="{{ route('admin.departments') }}"
        class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-300 hover:shadow-sm transition group">
        <p class="font-semibold text-gray-800 group-hover:text-indigo-600 transition">Gestión de departamentos</p>
        <p class="text-sm text-gray-400 mt-1">Crear, editar y eliminar departamentos</p>
    </a>
    <a href="{{ route('admin.companies') }}"
        class="bg-white rounded-xl border border-gray-200 p-5 hover:border-indigo-300 hover:shadow-sm transition group">
        <p class="font-semibold text-gray-800 group-hover:text-indigo-600 transition">Gestión de empresas</p>
        <p class="text-sm text-gray-400 mt-1">Crear, editar y eliminar empresas</p>
    </a>
</div>
@endsection
