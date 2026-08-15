@extends('layouts.app')

@section('title', 'Todos los Tickets')

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
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <!-- Header Area -->
    <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <h1 class="text-4xl font-black text-white tracking-tight">Todos los <span class="text-indigo-400">Tickets</span></h1>
            <p class="text-slate-400 mt-1">Explora y gestiona el listado global de incidencias del sistema.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
            @error('error')
                <div class="w-full sm:w-64 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-xs font-bold animate-pulse">
                    ⚠️ {{ $message }}
                </div>
            @enderror

            @if (session('success'))
                <div class="w-full sm:w-64 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-xs font-bold">
                    ✓ {{ session('success') }}
                </div>
            @endif
        </div>
    </header>

    <!-- Filter Bar -->
    <div class="synapse-glass p-6 mb-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="flex items-center gap-2 mb-2 lg:mb-0 min-w-max">
            <span class="w-2 h-2 bg-indigo-500 rounded-full animate-ping"></span>
            <h3 class="font-bold text-sm text-slate-300">Filtros Avanzados</h3>
        </div>
        
        <form method="GET" action="{{ route('support.all-tickets') }}" class="flex flex-wrap gap-3 w-full lg:justify-end">
            
            <select name="status" class="bg-slate-950 border border-slate-800 text-xs rounded-lg px-4 py-2.5 text-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none flex-grow sm:flex-grow-0 transition-colors">
                <option value="">Todos los estados</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>

            <select name="priority" class="bg-slate-950 border border-slate-800 text-xs rounded-lg px-4 py-2.5 text-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none flex-grow sm:flex-grow-0 transition-colors">
                <option value="">Todas las prioridades</option>
                @foreach($priorities as $p)
                    <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>

            <input type="text" name="assignee_id" placeholder="ID del agente" value="{{ request('assignee_id') }}" class="bg-slate-950 border border-slate-800 text-xs rounded-lg px-4 py-2.5 text-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none w-full sm:w-40 transition-colors">

            <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                <button type="submit" class="flex-1 sm:flex-none bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 px-5 py-2.5 rounded-lg text-xs font-bold hover:bg-indigo-600 hover:text-white transition-all">
                    Filtrar
                </button>
                <a href="{{ route('support.all-tickets') }}" class="flex-1 sm:flex-none text-center bg-slate-800 text-slate-400 px-5 py-2.5 rounded-lg text-xs font-bold hover:bg-slate-700 transition-all">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="synapse-glass overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-800 bg-slate-900/20">
                        <th class="px-6 py-5">Asunto / ID</th>
                        <th class="px-6 py-5">Prioridad</th>
                        <th class="px-6 py-5">Estado</th>
                        <th class="px-6 py-5">Solicitante</th>
                        <th class="px-6 py-5">Asignado a</th>
                        <th class="px-6 py-5 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-6 py-5">
                                <div class="font-bold text-white text-sm group-hover:text-indigo-300 transition-colors">{{ $ticket['title'] }}</div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">#TK-{{ str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center text-xs font-medium text-slate-300">
                                    <span @class([
                                        'w-2 h-2 rounded-full inline-block mr-2',
                                        'bg-red-500' => in_array(strtolower($ticket['priority']), ['high', 'alta', 'urgent']),
                                        'bg-amber-500' => in_array(strtolower($ticket['priority']), ['medium', 'media']),
                                        'bg-emerald-500' => !in_array(strtolower($ticket['priority']), ['high', 'alta', 'urgent', 'medium', 'media'])
                                    ])></span>
                                    {{ ucfirst($ticket['priority']) }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <span @class([
                                    'text-[0.65rem] font-extrabold uppercase px-3 py-1 rounded-full border',
                                    'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' => in_array(strtolower($ticket['status']), ['open', 'abierto']),
                                    'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' => in_array(strtolower($ticket['status']), ['resolved', 'resuelto', 'closed', 'cerrado']),
                                    'bg-amber-500/10 text-amber-400 border-amber-500/20' => in_array(strtolower($ticket['status']), ['in_progress', 'en progreso']),
                                    'bg-slate-500/10 text-slate-400 border-slate-500/20' => !in_array(strtolower($ticket['status']), ['open', 'abierto', 'resolved', 'resuelto', 'closed', 'cerrado', 'in_progress', 'en progreso'])
                                ])>
                                    {{ $ticket['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="inline-flex items-center gap-2 bg-slate-900/50 border border-slate-700/50 px-2.5 py-1 rounded-lg">
                                    <span class="text-slate-500 text-[10px] font-bold">ID:</span>
                                    <span class="text-slate-300 text-xs font-mono">{{ $ticket['requester_id'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @if($ticket['assignee_id'])
                                    <div class="inline-flex items-center gap-2 bg-indigo-900/20 border border-indigo-500/20 px-2.5 py-1 rounded-lg">
                                        <span class="text-indigo-500/70 text-[10px] font-bold">AG:</span>
                                        <span class="text-indigo-300 text-xs font-mono">{{ $ticket['assignee_id'] }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-600 text-xs font-medium italic">Sin asignar</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-right">
                                <a href="{{ route('support.ticket', $ticket['id']) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all shadow-inner">
                                    <span>→</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="text-slate-700 text-4xl mb-4 opacity-30">🔍</div>
                                <p class="text-slate-400 font-medium text-sm">No se encontraron tickets con los filtros aplicados.</p>
                                <a href="{{ route('support.all-tickets') }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-bold mt-2 inline-block">Restablecer filtros</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(!empty($tickets))
            <div class="p-6 bg-slate-900/40 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs">
                <p class="text-slate-500 font-medium">
                    Mostrando página <span class="text-white font-bold">{{ $meta['currentPage'] }}</span> de <span class="text-white font-bold">{{ $meta['totalPage'] }}</span>
                </p>
                <div class="flex items-center gap-3 bg-slate-950/50 px-4 py-2 rounded-lg border border-slate-800/50">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                    <span class="text-slate-300 font-bold">{{ $meta['total'] }} tickets encontrados</span>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection