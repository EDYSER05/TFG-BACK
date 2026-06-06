@extends('admin.layout')
@section('title', 'Departamentos')

@section('content')
<div class="flex justify-between items-center mb-4">
    <p class="text-sm text-gray-500">{{ count($departments) }} departamentos</p>
    <a href="{{ route('admin.departments.create') }}"
        class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        + Nuevo departamento
    </a>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('admin.departments') }}" class="flex gap-3 mb-4 flex-wrap items-center">
    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre..."
        class="flex-1 min-w-[220px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />

    {{-- Dropdown empresa --}}
    <div class="sd-container relative" id="sd-company">
        <input type="hidden" name="company_id" value="{{ $companyFilter }}">
        <button type="button" onclick="sdToggle('sd-company')"
            class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white hover:bg-gray-50 transition w-48 text-left">
            <span class="flex-1 truncate" id="sd-company-label">
                {{ $companyFilter ? $companies->firstWhere('id', $companyFilter)?->name ?? 'Todas las empresas' : 'Todas las empresas' }}
            </span>
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div id="sd-company-panel" class="hidden absolute top-full mt-1 z-20 bg-white border border-gray-200 rounded-lg shadow-lg w-48">
            <div class="p-2 border-b border-gray-100">
                <input type="text" placeholder="Buscar empresa..." oninput="sdFilter('sd-company', this.value)"
                    class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="max-h-48 overflow-y-auto" id="sd-company-options">
                <div class="sd-option px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 text-gray-500"
                    onclick="sdSelect('sd-company', '', 'Todas las empresas')">Todas las empresas</div>
                @foreach($companies as $company)
                <div class="sd-option px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 {{ $companyFilter == $company->id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700' }}"
                    onclick="sdSelect('sd-company', '{{ $company->id }}', '{{ addslashes($company->name) }}')">
                    {{ $company->name }}
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">Filtrar</button>
    @if($search || $companyFilter)
        <a href="{{ route('admin.departments') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition flex items-center">Limpiar</a>
    @endif
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
                <th class="px-5 py-3 text-left">Nombre</th>
                <th class="px-5 py-3 text-left">Empresa</th>
                <th class="px-5 py-3 text-left">Responsable</th>
                <th class="px-5 py-3 text-left">Estado</th>
                <th class="px-5 py-3 text-left"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($departments as $dept)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 font-medium text-gray-800">{{ $dept->name }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $dept->company?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-gray-500">
                    {{ $dept->manager ? $dept->manager->name . ' ' . $dept->manager->last_name : '—' }}
                </td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $dept->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $dept->active ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.departments.edit', $dept) }}"
                            class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Editar</a>
                        <form action="{{ route('admin.departments.toggle-active', $dept) }}" method="POST"
                            onsubmit="return confirm('¿{{ $dept->active ? 'Desactivar' : 'Activar' }} el departamento {{ addslashes($dept->name) }}?{{ $dept->active ? ' Esto desactivará también a todos sus empleados.' : ' Esto activará también a todos sus empleados.' }}')">
                            @csrf
                            <button type="submit" class="{{ $dept->active ? 'text-amber-500 hover:text-amber-700' : 'text-green-600 hover:text-green-800' }} text-xs font-medium">
                                {{ $dept->active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-8 text-center text-gray-400">
                    {{ $search || $companyFilter ? 'No se encontraron departamentos con esos filtros' : 'No hay departamentos registrados' }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
function sdToggle(id) {
    const panel = document.getElementById(id + '-panel');
    document.querySelectorAll('.sd-container [id$="-panel"]').forEach(p => {
        if (p.id !== id + '-panel') p.classList.add('hidden');
    });
    panel.classList.toggle('hidden');
    if (!panel.classList.contains('hidden')) {
        panel.querySelector('input[type="text"]').focus();
    }
}

function sdFilter(id, query) {
    const q = query.toLowerCase();
    document.querySelectorAll('#' + id + '-options .sd-option').forEach(opt => {
        opt.style.display = opt.textContent.trim().toLowerCase().includes(q) ? '' : 'none';
    });
}

function sdSelect(id, value, label) {
    document.querySelector('#' + id + ' input[type="hidden"]').value = value;
    document.getElementById(id + '-label').textContent = label;
    document.getElementById(id + '-panel').classList.add('hidden');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.sd-container')) {
        document.querySelectorAll('.sd-container [id$="-panel"]').forEach(p => p.classList.add('hidden'));
    }
});
</script>
@endsection
