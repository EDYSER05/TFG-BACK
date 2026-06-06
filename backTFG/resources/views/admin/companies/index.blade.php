@extends('admin.layout')
@section('title', 'Empresas')

@section('content')
<div class="flex justify-between items-center mb-4">
    <p class="text-sm text-gray-500">{{ $companies->count() }} empresas</p>
    <a href="{{ route('admin.companies.create') }}"
        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        + Nueva empresa
    </a>
</div>

{{-- Buscador --}}
<form method="GET" action="{{ route('admin.companies') }}" class="flex gap-3 mb-4">
    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o CIF/NIF..."
        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
    <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Buscar</button>
    @if($search)
        <a href="{{ route('admin.companies') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition flex items-center">Limpiar</a>
    @endif
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-5 py-3 text-left">Nombre</th>
                <th class="px-5 py-3 text-left">CIF/NIF</th>
                <th class="px-5 py-3 text-left">Propietario</th>
                <th class="px-5 py-3 text-left">Departamentos</th>
                <th class="px-5 py-3 text-left">Estado</th>
                <th class="px-5 py-3 text-left"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($companies as $company)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 font-medium text-gray-800">{{ $company->name }}</td>
                <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $company->tax_id }}</td>
                <td class="px-5 py-3 text-gray-500">
                    {{ $company->owner ? $company->owner->name . ' ' . $company->owner->last_name : '—' }}
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $company->departments->count() }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $company->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $company->active ? 'Activa' : 'Inactiva' }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.companies.edit', $company) }}"
                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Editar</a>
                        <form action="{{ route('admin.companies.toggle-active', $company) }}" method="POST"
                            onsubmit="return confirm('¿{{ $company->active ? 'Desactivar' : 'Activar' }} la empresa {{ addslashes($company->name) }}?{{ $company->active ? ' Esto desactivará también todos sus departamentos y empleados.' : ' Esto activará también todos sus departamentos y empleados.' }}')">
                            @csrf
                            <button type="submit" class="{{ $company->active ? 'text-amber-500 hover:text-amber-700' : 'text-green-600 hover:text-green-800' }} text-xs font-medium">
                                {{ $company->active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-8 text-center text-gray-400">
                    {{ $search ? 'No se encontraron empresas con esa búsqueda' : 'No hay empresas registradas' }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
