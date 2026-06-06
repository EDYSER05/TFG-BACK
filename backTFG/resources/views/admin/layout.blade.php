<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexTime Admin — @yield('title', 'Panel')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-56 bg-slate-900 flex flex-col h-screen sticky top-0 shrink-0">
        <div class="px-5 py-5 border-b border-slate-700">
            <h1 class="text-white font-bold text-lg tracking-tight">NexTime</h1>
            <p class="text-slate-400 text-xs mt-0.5">Panel de administración</p>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Dashboard
            </a>
            <a href="{{ route('admin.users') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.users*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Usuarios
            </a>
            <a href="{{ route('admin.departments') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.departments*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Departamentos
            </a>
            <a href="{{ route('admin.companies') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.companies*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                Empresas
            </a>
        </nav>

        <div class="px-3 py-4 border-t border-slate-700 shrink-0">
            <p class="px-3 text-xs text-slate-400 mb-2 truncate">
                {{ Auth::user()->name }} {{ Auth::user()->last_name }}
            </p>
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full text-left px-3 py-2 rounded-lg text-sm font-medium text-slate-300 hover:bg-red-900/40 hover:text-red-400 transition-colors">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- Contenido principal --}}
    <div class="flex-1 flex flex-col min-h-screen">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shrink-0">
            <h2 class="font-semibold text-gray-800">@yield('title', 'Panel')</h2>
            <span class="text-xs text-gray-400 bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Administrador</span>
        </header>

        <main class="flex-1 p-6">
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
