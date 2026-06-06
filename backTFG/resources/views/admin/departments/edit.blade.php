@extends('admin.layout')
@section('title', 'Editar departamento')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.departments') }}" class="text-sm text-indigo-600 hover:text-indigo-800 mb-4 inline-block">← Volver</a>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form action="{{ route('admin.departments.update', $department) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del departamento</label>
                <input type="text" name="name" value="{{ old('name', $department->name) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                <select name="company_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Seleccionar empresa</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id', $department->company_id) == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Responsable <span class="text-gray-400 font-normal">(opcional)</span></label>
                <select name="manager_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Sin responsable</option>
                    @foreach($managers as $manager)
                    <option value="{{ $manager->id }}" {{ old('manager_id', $department->manager_id) == $manager->id ? 'selected' : '' }}>
                        {{ $manager->name }} {{ $manager->last_name }} ({{ $manager->role?->name }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="active" id="active" value="1" class="rounded"
                    {{ old('active', $department->active) ? 'checked' : '' }} />
                <label for="active" class="text-sm text-gray-700 cursor-pointer">Departamento activo</label>
            </div>
            <div class="flex gap-3 pt-2">
                <a href="{{ route('admin.departments') }}"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
