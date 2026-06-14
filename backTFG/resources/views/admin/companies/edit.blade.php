@extends('admin.layout')
@section('title', 'Editar empresa')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form action="{{ route('admin.companies.update', $company) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la empresa</label>
                <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CIF / NIF</label>
                <input type="text" name="tax_id" value="{{ old('tax_id', $company->tax_id) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección <span class="text-gray-400 font-normal">(opcional)</span></label>
                <input type="text" name="address" value="{{ old('address', $company->address) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Propietario</label>
                <select name="owner_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Sin propietario</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('owner_id', $company->owner_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="active" id="active" value="1" class="rounded"
                    {{ old('active', $company->active) ? 'checked' : '' }} />
                <label for="active" class="text-sm text-gray-700 cursor-pointer">Empresa activa</label>
            </div>
            <div class="flex gap-3 pt-2">
                <a href="{{ route('admin.companies') }}"
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
