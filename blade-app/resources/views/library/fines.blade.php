@extends('layouts.app')

@section('title', 'Estado de Cuenta | Synapse Library')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chosen Palette: Synapse Midnight (Dark Slate & Indigo) -->
<!-- Application Structure Plan: 
    1. Header & Feedback: Alertas de éxito/error integradas en el diseño.
    2. KPI Analytics: Cálculo dinámico de deuda basado en el array $fines.
    3. Visual Insights: Gráfico de donut para ver la proporción de estados.
    4. Data Table: La tabla funcional del usuario con estilos premium y acciones POST.
-->

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

    .chart-container {
        position: relative;
        width: 100%;
        height: 250px;
    }

    .status-badge {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 0.25rem 0.75rem;
        border-radius: 99px;
    }

    .btn-action {
        transition: all 0.2s ease;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px -5px rgba(99, 102, 241, 0.5);
    }
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <!-- Header & Feedback -->
    <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-4xl font-black text-white tracking-tight">Gestión de <span class="text-indigo-400">Multas</span></h1>
            <p class="text-slate-400 mt-1">Revisa y liquida tus cargos pendientes por servicios bibliotecarios.</p>
        </div>

        <div class="w-full md:w-auto">
            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm font-bold animate-pulse">
                    ⚠️ {{ $errors->first('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-sm font-bold">
                    ✓ {{ session('success') }}
                </div>
            @endif
        </div>
    </header>

    @php
        $totalPending = 0;
        $totalPaid = 0;
        if(!empty($fines)) {
            foreach($fines as $f) {
                if($f['status'] === 'pending') $totalPending += $f['amount'];
                else $totalPaid += $f['amount'];
            }
        }
    @endphp

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="synapse-glass p-6 border-l-4 border-l-red-500">
            <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Deuda Pendiente</p>
            <p class="text-3xl font-black text-white">${{ number_format($totalPending, 2) }}</p>
        </div>
        <div class="synapse-glass p-6 border-l-4 border-l-indigo-500">
            <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Total Multas</p>
            <p class="text-3xl font-black text-white">{{ $meta['total'] ?? 0 }}</p>
        </div>
        <div class="synapse-glass p-6 border-l-4 border-l-emerald-500">
            <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Monto Pagado</p>
            <p class="text-3xl font-black text-emerald-400">${{ number_format($totalPaid, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <!-- Analytics -->
        <div class="synapse-glass p-6">
            <h3 class="text-sm font-bold text-slate-300 mb-6 uppercase tracking-widest">Distribución de Estados</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Filtros y Tabla -->
        <div class="lg:col-span-2 synapse-glass overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-slate-900/30">
                <h3 class="font-bold text-lg">Listado Detallado</h3>
                
                <form method="GET" action="{{ route('library.fines') }}" class="flex gap-2">
                    <select name="status" class="bg-slate-950 border border-slate-700 text-xs rounded-lg px-3 py-2 text-slate-300 outline-none focus:border-indigo-500">
                        <option value="">Todos los estados</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendientes</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pagadas</option>
                    </select>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-500 transition-colors">
                        Filtrar
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-800">
                            <th class="px-6 py-4">Monto</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4">Cronología</th>
                            <th class="px-6 py-4 text-right">Gestión</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse($fines as $fine)
                            <tr class="hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4 font-black text-white text-lg">
                                    ${{ number_format($fine['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($fine['status'] === 'pending')
                                        <span class="status-badge bg-red-500/10 text-red-400 border border-red-500/20">Pendiente</span>
                                    @else
                                        <span class="status-badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Pagada</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase">Generada:</div>
                                    <div class="text-sm text-slate-300">{{ $fine['created_at'] ? \Carbon\Carbon::parse($fine['created_at'])->format('d/m/Y') : '—' }}</div>
                                    @if($fine['paid_at'])
                                        <div class="text-[10px] text-emerald-500 font-bold uppercase mt-1">Pagada:</div>
                                        <div class="text-sm text-emerald-400/70">{{ \Carbon\Carbon::parse($fine['paid_at'])->format('d/m/Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($fine['status'] === 'pending')
                                        <form method="POST" action="{{ route('library.fine.pay', $fine['id']) }}">
                                            @csrf
                                            <button type="submit" class="btn-action px-4 py-2 rounded-xl text-xs font-bold text-white">
                                                Pagar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs font-bold text-slate-600 italic">Sin acciones</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500 font-medium">
                                    No tienes multas registradas en esta categoría.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(!empty($fines))
                <div class="p-6 bg-slate-900/50 flex justify-between items-center text-xs">
                    <p class="text-slate-400">
                        Página <span class="text-white font-bold">{{ $meta['currentPage'] }}</span> de <span class="text-white font-bold">{{ $meta['totalPage'] }}</span>
                    </p>
                    <p class="text-indigo-400 font-bold">{{ $meta['total'] }} registros encontrados</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('statusChart').getContext('2d');
        
        // Datos calculados desde PHP para el gráfico
        const pendingCount = {{ collect($fines)->where('status', 'pending')->count() }};
        const paidCount = {{ collect($fines)->where('status', 'paid')->count() }};

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pendientes', 'Pagadas'],
                datasets: [{
                    data: [pendingCount, paidCount],
                    backgroundColor: ['#f43f5e', '#10b981'],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#94a3b8',
                            font: { size: 11, weight: 'bold' },
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    });
</script>

@endsection