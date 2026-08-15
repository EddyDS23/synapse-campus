@extends('layouts.app')

@section('title', 'Mis Préstamos | Synapse')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        background-color: #020617; /* Slate 950 */
        color: #f1f5f9;
    }
    .synapse-card {
        background: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1.5rem;
        transition: all 0.3s ease;
    }
    .synapse-card:hover {
        border-color: #6366f1;
        background: rgba(30, 41, 59, 0.6);
    }
    .status-badge {
        font-size: 0.65rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.25rem 0.75rem;
        border-radius: 99px;
    }
    .btn-action {
        padding: 0.6rem 1rem;
        border-radius: 0.75rem;
        font-weight: 700;
        font-size: 0.875rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .progress-bar {
        height: 6px;
        background: #1e293b;
        border-radius: 99px;
        overflow: hidden;
    }
    .chart-container {
        position: relative;
        height: 180px;
        width: 100%;
    }
</style>

<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    
    <!-- Header de Gestión -->
    <header class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black text-white tracking-tight">Mis <span class="text-indigo-400">Préstamos</span></h1>
            <p class="text-slate-400 mt-2">Gestiona tus plazos de devolución y solicitudes de renovación.</p>
        </div>

        <div class="flex flex-col gap-3 min-w-[320px]">
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-3 animate-pulse">
                    <span class="bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">!</span>
                    {{ $errors->first('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-3">
                    <span class="bg-emerald-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[10px]">✓</span>
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </header>

    @if(empty($loans))
        <div class="synapse-card p-20 text-center flex flex-col items-center">
            <div class="w-20 h-20 bg-indigo-500/10 rounded-full flex items-center justify-center mb-6">
                <span class="text-4xl">📚</span>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">Tu estante está vacío</h3>
            <p class="text-slate-500 mb-8 max-w-sm">No tienes préstamos activos en este momento. Explora nuestro catálogo para comenzar a aprender.</p>
            <a href="{{ route('library.books') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white px-8 py-4 rounded-2xl font-black transition-all shadow-xl shadow-indigo-900/20">
                Explorar Catálogo →
            </a>
        </div>
    @else
        <!-- Dashboard de Estado de Préstamos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <div class="synapse-card p-6">
                <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">Tiempo de Entrega Promedio</h3>
                <div class="chart-container">
                    <canvas id="loanTimeChart"></canvas>
                </div>
            </div>

            <div class="lg:col-span-2 synapse-card p-8 flex flex-col md:flex-row items-center gap-8">
                <div class="text-center md:text-left">
                    <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Resumen de Cuenta</p>
                    <h2 class="text-3xl font-black text-white mb-4">Estado de Salud</h2>
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                        <div class="bg-slate-900/50 p-4 rounded-2xl border border-slate-800">
                            <p class="text-[10px] font-bold text-slate-500 uppercase">Libros Activos</p>
                            <p class="text-2xl font-black text-indigo-400">{{ count($loans) }}</p>
                        </div>
                        <div class="bg-slate-900/50 p-4 rounded-2xl border border-slate-800">
                            <p class="text-[10px] font-bold text-slate-500 uppercase">Crédito Disponible</p>
                            <p class="text-2xl font-black text-emerald-400">{{ 5 - count($loans) }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex-grow w-full">
                    <div class="p-6 bg-indigo-600/10 border border-indigo-500/20 rounded-3xl">
                        <p class="text-sm font-bold text-indigo-200 mb-2 italic">"El conocimiento es la única herramienta que crece cuando se comparte."</p>
                        <p class="text-xs text-indigo-400/60 font-bold">— Synapse Philosophy</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Préstamos -->
        <div class="grid grid-cols-1 gap-4">
            @foreach($loans as $loan)
                @php
                    $dueAt = \Carbon\Carbon::parse($loan['due_at']);
                    $daysRemaining = now()->diffInDays($dueAt, false);
                    $percentage = max(0, min(100, (now()->diffInDays(\Carbon\Carbon::parse($loan['borrowed_at'])) / \Carbon\Carbon::parse($loan['borrowed_at'])->diffInDays($dueAt)) * 100));
                    $isOverdue = $daysRemaining < 0;
                @endphp

                <div class="synapse-card p-6 flex flex-col md:flex-row items-center gap-6 group">
                    <!-- Icono/Libro -->
                    <div class="w-16 h-20 bg-gradient-to-br from-slate-700 to-slate-900 rounded-lg flex flex-col items-center justify-center border border-slate-700 shadow-lg shrink-0">
                        <span class="text-[10px] font-black text-slate-500 uppercase">ID</span>
                        <span class="text-lg font-black text-indigo-400">{{ $loan['book_id'] }}</span>
                    </div>

                    <!-- Info Principal -->
                    <div class="flex-grow text-center md:text-left">
                        <div class="flex flex-col md:flex-row md:items-center gap-3 mb-2 justify-center md:justify-start">
                            <h4 class="text-xl font-bold text-white group-hover:text-indigo-300 transition-colors">Código de Ejemplar: #{{ $loan['id'] }}</h4>
                            @if($loan['status'] === 'active')
                                <span class="status-badge {{ $isOverdue ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                                    {{ $isOverdue ? 'Vencido' : 'En tiempo' }}
                                </span>
                            @else
                                <span class="status-badge bg-slate-700 text-slate-300">{{ $loan['status'] }}</span>
                            @endif
                        </div>
                        
                        <div class="flex flex-wrap justify-center md:justify-start gap-x-6 gap-y-2 text-sm">
                            <p class="text-slate-400"><span class="text-slate-500 font-bold uppercase text-[10px] block">Prestado</span> {{ \Carbon\Carbon::parse($loan['borrowed_at'])->format('d M, Y') }}</p>
                            <p class="text-slate-400"><span class="text-slate-500 font-bold uppercase text-[10px] block">Devolución</span> {{ $dueAt->format('d M, Y') }}</p>
                            <p class="text-slate-400"><span class="text-slate-500 font-bold uppercase text-[10px] block">Renovaciones</span> {{ $loan['renew_count'] }} / 3</p>
                        </div>
                    </div>

                    <!-- Indicador Visual de Tiempo -->
                    <div class="w-full md:w-48">
                        <div class="flex justify-between text-[10px] font-bold uppercase mb-2">
                            <span class="{{ $isOverdue ? 'text-red-400' : 'text-slate-500' }}">
                                {{ $isOverdue ? 'Retraso de ' . abs($daysRemaining) . ' días' : 'Faltan ' . $daysRemaining . ' días' }}
                            </span>
                            <span class="text-slate-500">{{ round($percentage) }}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="h-full {{ $isOverdue ? 'bg-red-500' : 'bg-indigo-500' }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex gap-2 shrink-0">
                        @if($loan['status'] === 'active')
                            <form method="POST" action="{{ route('library.loan.renew', $loan['id']) }}">
                                @csrf
                                <button type="submit" class="btn-action bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700" {{ $loan['renew_count'] >= 3 ? 'disabled' : '' }}>
                                    <span>🔄</span> Renovar
                                </button>
                            </form>
                            <form method="POST" action="{{ route('library.loan.return', $loan['id']) }}">
                                @csrf
                                <button type="submit" class="btn-action bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white border border-indigo-500/30">
                                    <span>📥</span> Devolver
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('loanTimeChart').getContext('2d');
        
        // Simulación de datos para el gráfico de tiempo
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'],
                datasets: [{
                    label: 'Páginas Leídas (Est.)',
                    data: [12, 19, 15, 25, 32, 45, 40],
                    borderColor: '#6366f1',
                    borderWidth: 3,
                    pointRadius: 0,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(99, 102, 241, 0.1)'
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    });
</script>
@endsection