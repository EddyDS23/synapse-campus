@extends('layouts.app')

@section('title', 'Auditoría | AuthVault')

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
    <header class="mb-10">
        <h1 class="text-4xl font-black text-white tracking-tight">Registro de <span class="text-indigo-400">Auditoría</span></h1>
        <p class="text-slate-400 mt-1">Historial completo de acciones y eventos registrados en AuthVault.</p>
    </header>

    <!-- Audit Logs Table Container -->
    <div class="synapse-glass overflow-hidden">
        <div class="p-6 border-b border-slate-800/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="font-black text-white text-lg tracking-tight">Bitácora del Sistema</h2>
            </div>
            @if(isset($meta['total']))
                <div class="bg-slate-900/60 border border-slate-800 px-3 py-1.5 rounded-lg text-xs font-mono text-indigo-300">
                    {{ $meta['total'] }} eventos totales
                </div>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-800 bg-slate-900/20">
                        <th class="px-6 py-4">Acción</th>
                        <th class="px-6 py-4">Usuario ID</th>
                        <th class="px-6 py-4">Servicio</th>
                        <th class="px-6 py-4">Recurso Afectado</th>
                        <th class="px-6 py-4">Dirección IP</th>
                        <th class="px-6 py-4 text-right">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-6 py-4 text-sm font-bold text-slate-200 group-hover:text-indigo-300 transition-colors">
                                {{ $log['action'] }}
                            </td>
                            <td class="px-6 py-4">
                                @if(!empty($log['user_id']))
                                    <div class="inline-flex items-center gap-1.5 bg-slate-900/50 border border-slate-800 px-2.5 py-1 rounded-md text-xs font-mono text-slate-300">
                                        <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
                                        {{ $log['user_id'] }}
                                    </div>
                                @else
                                    <span class="text-slate-600 font-bold">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if(!empty($log['service']))
                                    <span class="inline-block bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-md">
                                        {{ $log['service'] }}
                                    </span>
                                @else
                                    <span class="text-slate-600 font-bold">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if(!empty($log['resource_type']))
                                    <div class="flex items-center gap-2 text-xs text-slate-300">
                                        <span class="font-medium">{{ $log['resource_type'] }}</span>
                                        @if(!empty($log['resource_id']))
                                            <span class="bg-slate-950 border border-slate-800 px-2 py-0.5 rounded text-[10px] font-mono text-slate-400">
                                                {{ substr($log['resource_id'], 0, 8) }}...
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-600 font-bold">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-1.5 bg-slate-900/50 border border-slate-700/50 px-2 py-1 rounded text-xs font-mono text-slate-400">
                                    {{ $log['ip_address'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-slate-400 font-medium">
                                {{ \Carbon\Carbon::parse($log['created_at'])->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="text-slate-700 text-4xl mb-3 opacity-30">📭</div>
                                <p class="text-slate-400 font-medium text-sm">No hay registros de auditoría disponibles.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($meta['total']) && isset($meta['current_page']) && isset($meta['last_page']))
            <div class="p-6 bg-slate-900/40 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs">
                <p class="text-slate-500 font-medium">
                    Mostrando página <span class="text-white font-bold">{{ $meta['current_page'] }}</span> de <span class="text-white font-bold">{{ $meta['last_page'] }}</span>
                </p>
                <div class="flex items-center gap-3 bg-slate-950/50 px-4 py-2 rounded-lg border border-slate-800/50">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                    <span class="text-slate-300 font-bold">{{ $meta['total'] }} registros en total</span>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection