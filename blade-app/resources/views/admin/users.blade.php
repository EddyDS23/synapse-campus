@extends('layouts.app')

@section('title', 'Gestión de Usuarios | Synapse Admin')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<style>
    :root {
        --bg: #020617;
        --card-bg: rgba(30, 41, 59, 0.5);
        --accent: #6366f1;
    }

    body {
        background-color: var(--bg);
        color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .synapse-glass {
        background: var(--card-bg);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -5px rgba(99, 102, 241, 0.6);
    }
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <!-- Header Area -->
    <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <h1 class="text-4xl font-black text-white tracking-tight">Gestión de <span class="text-indigo-400">Usuarios</span></h1>
            <p class="text-slate-400 mt-1">Asigna y revoca roles del ecosistema administrativo.</p>
        </div>

        <div class="flex flex-col gap-3 w-full md:w-auto">
            @if($errors->any())
                <div class="w-full sm:w-72 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-xs font-bold animate-pulse">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif
            @if(session('success'))
                <div class="w-full sm:w-72 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-xs font-bold">
                    ✓ {{ session('success') }}
                </div>
            @endif
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Asignar rol --}}
        <div class="synapse-glass p-6 border-t-2 border-t-indigo-500 flex flex-col">
            <div class="border-b border-slate-800/50 pb-4 mb-6">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="font-black text-white text-lg tracking-tight">Asignar rol</h2>
                </div>
                <p class="text-xs text-slate-400 font-medium">Agrega un rol a un usuario existente.</p>
            </div>
            
            <form method="POST" action="{{ route('admin.roles.assign') }}" class="flex flex-col flex-1 gap-5">
                @csrf
                <div>
                    <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">ULID del usuario *</label>
                    <input type="text" name="user_id" required placeholder="01kvmbb1km2r6mmma5bd5x790n"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all text-sm font-mono placeholder-slate-600">
                </div>
                <div>
                    <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Rol *</label>
                    <select name="role" required class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all text-sm appearance-none">
                        <option value="" class="bg-slate-900 text-slate-500">Seleccionar rol...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" class="bg-slate-900">{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-auto pt-2">
                    <button type="submit" class="btn-primary w-full text-white font-bold py-3 rounded-xl text-sm">
                        Asignar rol
                    </button>
                </div>
            </form>
        </div>

        {{-- Revocar rol --}}
        <div class="synapse-glass p-6 border-t-2 border-t-red-500 flex flex-col">
            <div class="border-b border-slate-800/50 pb-4 mb-6">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="font-black text-white text-lg tracking-tight">Revocar rol</h2>
                </div>
                <p class="text-xs text-slate-400 font-medium">Elimina un rol asociado a un usuario.</p>
            </div>
            
            <form method="POST" action="" id="revokeForm" class="flex flex-col flex-1 gap-5">
                @csrf
                @method('DELETE')
                <div>
                    <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">ULID del usuario *</label>
                    <input type="text" id="revokeUserId" required placeholder="01kvmbb1km2r6mmma5bd5x790n"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-200 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition-all text-sm font-mono placeholder-slate-600">
                </div>
                <div>
                    <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Rol *</label>
                    <select id="revokeRole" required class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-200 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition-all text-sm appearance-none">
                        <option value="" class="bg-slate-900 text-slate-500">Seleccionar rol...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" class="bg-slate-900">{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-auto pt-2">
                    <button type="button" onclick="submitRevoke()" class="w-full bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500 hover:text-white font-bold py-3 rounded-xl text-sm transition-all shadow-sm">
                        Revocar rol
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- Security status lookup --}}
    <div class="synapse-glass p-6">
        <div class="border-b border-slate-800/50 pb-4 mb-6">
            <div class="flex items-center gap-2 mb-1">
                <h2 class="font-black text-white text-lg tracking-tight">Consultar estado de seguridad</h2>
            </div>
            <p class="text-xs text-slate-400 font-medium">Verifica el estado de cuenta y sesiones de un usuario por su ULID.</p>
        </div>
        
        <form method="GET" action="{{ route('admin.security-status') }}" class="flex flex-col sm:flex-row gap-4 items-end mb-6">
            <div class="w-full sm:flex-1">
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">ULID del usuario</label>
                <input type="text" name="user_id" value="{{ request('user_id') }}" placeholder="01kvmbb1km2r6mmma5bd5x790n"
                    class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all text-sm font-mono placeholder-slate-600">
            </div>
            <button type="submit" class="w-full sm:w-auto bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 hover:text-white font-bold px-6 py-3 rounded-xl text-sm transition-all">
                Consultar
            </button>
        </form>

        @if(isset($data) && $data)
            <div class="p-6 bg-slate-900/60 border border-slate-700/50 rounded-2xl">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    
                    <div class="col-span-2 md:col-span-1">
                        <div class="text-[10px] font-bold tracking-widest text-slate-500 uppercase mb-2">User ID</div>
                        <div class="font-mono text-xs text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1.5 rounded-lg inline-block">
                            {{ $data['user_id'] }}
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] font-bold tracking-widest text-slate-500 uppercase mb-2">Estado 2FA</div>
                        <div>
                            @if($data['two_factor_enabled'])
                                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-3 py-1 rounded-md text-[10px] font-extrabold uppercase inline-block">
                                    Activo
                                </span>
                            @else
                                <span class="bg-slate-500/10 text-slate-400 border border-slate-500/20 px-3 py-1 rounded-md text-[10px] font-extrabold uppercase inline-block">
                                    Inactivo
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] font-bold tracking-widest text-slate-500 uppercase mb-2">Bloqueo de Cuenta</div>
                        <div>
                            @if($data['account_blocked'])
                                <span class="bg-red-500/10 text-red-400 border border-red-500/20 px-3 py-1 rounded-md text-[10px] font-extrabold uppercase inline-block animate-pulse">
                                    Bloqueada
                                </span>
                            @else
                                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-3 py-1 rounded-md text-[10px] font-extrabold uppercase inline-block">
                                    Activa
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="col-span-2 md:col-span-1 border-t border-slate-800 md:border-t-0 md:border-l pl-0 md:pl-6 pt-4 md:pt-0">
                        <div class="text-[10px] font-bold tracking-widest text-slate-500 uppercase mb-2">Sesiones Activas</div>
                        <div class="text-3xl font-black text-white">
                            {{ $data['active_sessions'] }}
                        </div>
                    </div>

                    <div class="col-span-2 md:col-span-4 border-t border-slate-800 pt-4 mt-2">
                        <div class="text-[10px] font-bold tracking-widest text-slate-500 uppercase mb-1">Último Acceso Registrado</div>
                        <div class="text-sm font-mono text-slate-300">
                            {{ $data['last_login'] ? \Carbon\Carbon::parse($data['last_login'])->format('d M Y, H:i') : 'Sin registros previos' }}
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>
</div>

<script>
function submitRevoke() {
    const userId = document.getElementById('revokeUserId').value.trim();
    const role   = document.getElementById('revokeRole').value;
    
    if (!userId || !role) {
        alert("Por favor, ingrese el ULID y seleccione un rol para revocar.");
        return;
    }
    
    const form = document.getElementById('revokeForm');
    form.action = '/admin/users/' + userId + '/roles/' + role;
    form.submit();
}
</script>
@endsection