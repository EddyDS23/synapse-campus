@extends('layouts.app')

@section('title', 'Mi Perfil - Synapse Campus')

@section('content')
<!-- Recursos para el acabado Nocturno Premium -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .dark-card {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(10px);
        border: 1px solid var(--border);
        border-radius: 2rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dark-card:hover {
        border-color: var(--accent);
        transform: translateY(-5px);
        box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.15);
    }
    .profile-avatar {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        box-shadow: 0 0 40px rgba(99, 102, 241, 0.3);
    }
    .chart-container {
        position: relative;
        width: 100%;
        height: 250px;
    }
    .status-pulse {
        position: relative;
        display: flex;
        height: 10px;
        width: 10px;
    }
    .status-pulse span {
        position: absolute;
        display: inline-flex;
        height: 100%;
        width: 100%;
        border-radius: 9999px;
        opacity: 0.75;
    }
    .animate-ping-custom {
        animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    @keyframes ping {
        75%, 100% { transform: scale(2.5); opacity: 0; }
    }
</style>

<div class="max-w-6xl mx-auto p-6 lg:p-12 animate-fade-in">
    
    <!-- Hero Header: Identidad del Usuario -->
    <div class="flex flex-col md:flex-row items-center gap-10 mb-16">
        <div class="profile-avatar w-32 h-32 md:w-48 md:h-48 rounded-[3.5rem] flex items-center justify-center text-white text-5xl md:text-7xl font-black shrink-0">
            {{ strtoupper(substr(session('email'), 0, 1)) }}
        </div>
        
        <div class="text-center md:text-left flex-grow">
            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-bold uppercase tracking-[0.2em] mb-6">
                <span class="status-pulse">
                    <span class="animate-ping-custom bg-indigo-400"></span>
                    <span class="bg-indigo-500"></span>
                </span>
                Expediente Digital Verificado
            </div>
            <h1 class="text-5xl md:text-7xl font-black tracking-tighter mb-3 text-white">
                {{ explode('@', session('email'))[0] }}
            </h1>
            <p class="text-slate-400 text-xl font-light italic">{{ $career }}</p>
        </div>

        <div class="flex flex-col gap-3 w-full md:w-auto">
            <button class="px-8 py-4 bg-white/5 border border-white/10 hover:bg-white/10 text-white rounded-2xl font-bold transition-all">
                Editar Información
            </button>
            <button class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-bold shadow-xl shadow-indigo-600/20 transition-all">
                Exportar Kárdex
            </button>
        </div>
    </div>

    <!-- Grid Principal de Datos -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Columna de Datos Técnicos (Izquierda) -->
        <div class="lg:col-span-4 space-y-8">
            <div class="dark-card p-8">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-8 text-center md:text-left">Datos de Control</p>
                
                <div class="space-y-8">
                    <div>
                        <p class="text-slate-500 text-xs uppercase font-semibold mb-2">Número de Control</p>
                        <p class="text-2xl font-mono font-bold text-white tracking-widest">{{ $number }}</p>
                    </div>
                    
                    <div class="pt-6 border-t border-white/5">
                        <p class="text-slate-500 text-xs uppercase font-semibold mb-2">Semestre Actual</p>
                        <p class="text-4xl font-black text-indigo-400">{{ $semester }}° <span class="text-lg font-light text-slate-500">Ciclo</span></p>
                    </div>

                    <div class="pt-6 border-t border-white/5">
                        <p class="text-slate-500 text-xs uppercase font-semibold mb-2">Estatus Académico</p>
                        <div class="inline-block px-4 py-2 rounded-xl bg-teal-500/10 text-teal-400 border border-teal-500/20 font-bold mt-2">
                            {{ strtoupper($status) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Adeudo Dinámica -->
            <div class="dark-card p-8 border-l-4 {{ $has_debt ? 'border-l-red-500 bg-red-500/5' : 'border-l-teal-500 bg-teal-500/5' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase mb-1">Estado Financiero</p>
                        <h4 class="text-xl font-bold {{ $has_debt ? 'text-red-400' : 'text-teal-400' }}">
                            {{ $has_debt ? 'Pendiente de Pago' : 'Sin Adeudos' }}
                        </h4>
                    </div>
                    <div class="text-3xl">
                        {{ $has_debt ? '⚠' : '✓' }}
                    </div>
                </div>
                <p class="text-slate-500 text-xs mt-4 leading-relaxed">
                    {{ $has_debt ? 'Tienes trámites pendientes en tesorería. Favor de acudir a ventanilla.' : 'Tu cuenta está al corriente con todos los servicios institucionales.' }}
                </p>
            </div>
        </div>

        <!-- Columna de Visualización (Derecha) -->
        <div class="lg:col-span-8 space-y-8">
            <div class="dark-card p-10 h-full flex flex-col">
                <div class="flex justify-between items-start mb-10">
                    <div>
                        <h3 class="text-2xl font-bold text-white mb-2">Actividad en Plataforma</h3>
                        <p class="text-slate-500 text-sm">Frecuencia de acceso y participación en el campus virtual.</p>
                    </div>

                </div>
                
                <p class="text-slate-500 text-sm">Los datos de actividad se mostrarán cuando estén disponibles desde el servicio académico.</p>


            </div>
        </div>
    </div>
</div>


@endsection