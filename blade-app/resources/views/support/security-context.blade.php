@extends('layouts.app')

@section('title', 'Contexto de Seguridad | Synapse Support')

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

    .security-metric {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1rem;
        padding: 1.25rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <!-- Header Area -->
    <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <h1 class="text-4xl font-black text-white tracking-tight">Contexto de <span class="text-indigo-400">Seguridad</span></h1>
            <p class="text-slate-400 mt-1">Análisis de riesgo y estado de autenticación del usuario.</p>
        </div>

        <a href="{{ route('support.ticket', $ticket['id'] ?? '') }}" class="bg-slate-800 text-slate-300 border border-slate-700 px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-slate-700 hover:text-white transition-all flex items-center gap-2 shadow-lg">
            <span>←</span> Volver al Ticket
        </a>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Columna Izquierda: Información del Ticket -->
        <div class="lg:col-span-1">
            <div class="synapse-glass p-6 sticky top-6">
                <h2 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-5 border-b border-slate-800/50 pb-3">Referencia</h2>
                
                <div class="space-y-5">
                    <div>
                        <span class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Título del Ticket</span>
                        <span class="text-sm font-medium text-white">{{ $ticket['title'] ?? '—' }}</span>
                    </div>

                    <div>
                        <span class="block text-[10px] text-slate-500 uppercase font-bold mb-1">Estado</span>
                        <span class="inline-block bg-slate-800 text-slate-300 text-[10px] font-extrabold uppercase px-3 py-1 rounded-full border border-slate-700">
                            {{ $ticket['status'] ?? '—' }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-[10px] text-slate-500 uppercase font-bold mb-1">ID Solicitante</span>
                        <div class="inline-flex items-center gap-2 bg-indigo-950/30 border border-indigo-500/20 px-3 py-1.5 rounded-lg">
                            <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                            <span class="text-indigo-300 text-xs font-mono font-bold">{{ $ticket['requester_id'] ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Métricas de Seguridad -->
        <div class="lg:col-span-2">
            <div class="synapse-glass p-6 h-full">
                <div class="flex items-center gap-2 border-b border-slate-800/50 pb-3 mb-6">
                    <span class="text-xl">🛡️</span>
                    <h2 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Auditoría de Cuenta</h2>
                </div>

                @if(empty($securityContext))
                    <div class="flex flex-col items-center justify-center py-12 px-4 text-center bg-slate-900/30 rounded-2xl border border-dashed border-slate-700">
                        <span class="text-4xl opacity-30 mb-3">⚠️</span>
                        <p class="text-slate-400 text-sm font-medium">No se pudo obtener el contexto de seguridad para este usuario.</p>
                        <p class="text-slate-600 text-xs mt-1">El servicio de auditoría podría estar temporalmente fuera de línea.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Métrica: 2FA -->
                        <div class="security-metric {{ $securityContext['two_factor_enabled'] ? 'border-emerald-500/20' : 'border-amber-500/30' }}">
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Autenticación 2FA</span>
                                <span class="text-lg">{{ $securityContext['two_factor_enabled'] ? '🔒' : '🔓' }}</span>
                            </div>
                            <div>
                                @if($securityContext['two_factor_enabled'])
                                    <span class="text-emerald-400 font-black text-xl">Activo</span>
                                    <p class="text-[10px] text-slate-500 mt-1">Protección de capa adicional habilitada.</p>
                                @else
                                    <span class="text-amber-400 font-black text-xl">Inactivo</span>
                                    <p class="text-[10px] text-slate-500 mt-1">El usuario es vulnerable a ataques de credenciales.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Métrica: Sesiones -->
                        <div class="security-metric">
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Sesiones Activas</span>
                                <span class="text-lg">💻</span>
                            </div>
                            <div>
                                <span class="text-white font-black text-2xl">{{ $securityContext['active_sessions'] }}</span>
                                <p class="text-[10px] text-slate-500 mt-1">Dispositivos conectados actualmente.</p>
                            </div>
                        </div>

                        <!-- Métrica: Último Login -->
                        <div class="security-metric">
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Último Acceso</span>
                                <span class="text-lg">⏱️</span>
                            </div>
                            <div>
                                <span class="text-indigo-300 font-mono text-sm font-bold block mb-1">
                                    {{ $securityContext['last_login'] ? \Carbon\Carbon::parse($securityContext['last_login'])->format('d M Y') : '—' }}
                                </span>
                                <span class="text-white text-xs">
                                    {{ $securityContext['last_login'] ? \Carbon\Carbon::parse($securityContext['last_login'])->format('H:i:s') : 'Sin registros' }}
                                </span>
                            </div>
                        </div>

                        <!-- Métrica: Bloqueo de cuenta -->
                        <div class="security-metric {{ $securityContext['account_blocked'] ? 'bg-red-500/10 border-red-500/30' : '' }}">
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Estado de Cuenta</span>
                                <span class="text-lg">{{ $securityContext['account_blocked'] ? '🚫' : '✅' }}</span>
                            </div>
                            <div>
                                @if($securityContext['account_blocked'])
                                    <span class="text-red-400 font-black text-xl">Bloqueada</span>
                                    <p class="text-[10px] text-red-500/70 mt-1 font-bold">Requiere intervención administrativa.</p>
                                @else
                                    <span class="text-emerald-400 font-black text-xl">Operativa</span>
                                    <p class="text-[10px] text-slate-500 mt-1">Sin restricciones de seguridad.</p>
                                @endif
                            </div>
                        </div>

                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection