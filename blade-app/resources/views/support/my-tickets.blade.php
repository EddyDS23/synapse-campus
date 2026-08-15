@extends('layouts.app')

@section('title', 'Mis Tickets | Synapse Support')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chosen Palette: Synapse Midnight (Dark Slate, Indigo & Amber) -->
<!-- Application Structure Plan: 
    1. Header & Quick Actions: Link to create new tickets and system alerts.
    2. Support Analytics: KPI cards and a Chart.js doughnut to visualize ticket resolution status.
    3. Advanced Filtering: Integrated search and status filter bar.
    4. Interactive Ticket List: A refined table with status badges and priority indicators.
-->

<!-- Visualization & Content Choices:
    - Status Chart -> Goal: Inform -> Breakdown of ticket statuses.
    - Priority Badges -> Goal: Highlight -> Immediate identification of urgent issues.
    - KPI Cards -> Goal: Summarize -> Rapid overview of support activity.
    - CONFIRMATION: NO SVG graphics used. NO Mermaid JS used.
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
        height: 220px;
    }

    .status-badge {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 0.25rem 0.75rem;
        border-radius: 99px;
    }

    .priority-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
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
            <h1 class="text-4xl font-black text-white tracking-tight">Centro de <span class="text-indigo-400">Soporte</span></h1>
            <p class="text-slate-400 mt-1">Gestiona tus consultas técnicas y reportes de incidencia.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
            @if ($errors->any())
                <div class="w-full sm:w-64 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-xs font-bold animate-pulse">
                    ⚠️ {{ $errors->first('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="w-full sm:w-64 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-xs font-bold">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <a href="{{ route('support.create') }}" class="btn-primary w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-bold text-white text-center shadow-lg">
                + Nuevo Ticket
            </a>
        </div>
    </header>

    <!-- Analytics Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10">
        <!-- KPI Cards -->
        <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="synapse-glass p-6 border-l-4 border-l-indigo-500">
                <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Total Registrados</p>
                <p class="text-3xl font-black text-white">{{ $meta['total'] ?? 0 }}</p>
            </div>
            <div class="synapse-glass p-6 border-l-4 border-l-amber-500">
                <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">En Proceso</p>
                <p class="text-3xl font-black text-white">
                    {{ collect($tickets)->whereIn('status', ['open', 'in_progress', 'Pending'])->count() }}
                </p>
            </div>
            <div class="synapse-glass p-6 border-l-4 border-l-emerald-500">
                <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Resueltos</p>
                <p class="text-3xl font-black text-emerald-400">
                    {{ collect($tickets)->whereIn('status', ['resolved', 'closed', 'Resolved', 'Closed'])->count() }}
                </p>
            </div>
            
            <!-- Filter Bar as a Wide Card -->
            <div class="md:col-span-3 synapse-glass p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full animate-ping"></span>
                    <h3 class="font-bold text-sm text-slate-300">Explorador de Incidencias</h3>
                </div>
                
                <form method="GET" action="{{ route('support.my-tickets') }}" class="flex flex-wrap gap-2 w-full md:w-auto">
                    <select name="status" class="bg-slate-950 border border-slate-700 text-xs rounded-lg px-4 py-2 text-slate-300 focus:border-indigo-500 outline-none flex-grow md:flex-grow-0">
                        <option value="">Todos los estados</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-600 hover:text-white transition-all">
                        Filtrar
                    </button>
                    <a href="{{ route('support.my-tickets') }}" class="bg-slate-800 text-slate-400 px-4 py-2 rounded-lg text-xs font-bold hover:bg-slate-700 transition-all">
                        Limpiar
                    </a>
                </form>
            </div>
        </div>

        <!-- Chart -->
        <div class="synapse-glass p-6 flex flex-col items-center">
            <h3 class="text-[10px] font-bold text-slate-500 uppercase mb-6 self-start">Distribución de Estados</h3>
            <div class="chart-container">
                <canvas id="ticketStatusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="synapse-glass overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-800">
                        <th class="px-6 py-5">Asunto / ID</th>
                        <th class="px-6 py-5">Prioridad</th>
                        <th class="px-6 py-5">Estado</th>
                        <th class="px-6 py-5">Cronología de Cierre</th>
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
                                    @php
                                        $pColor = match(strtolower($ticket['priority'])) {
                                            'high', 'alta', 'urgent' => 'bg-red-500',
                                            'medium', 'media' => 'bg-amber-500',
                                            default => 'bg-emerald-500'
                                        };
                                    @endphp
                                    <span class="priority-indicator {{ $pColor }}"></span>
                                    {{ ucfirst($ticket['priority']) }}
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @php
                                    $sStyle = match(strtolower($ticket['status'])) {
                                        'open', 'abierto' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                        'resolved', 'resuelto', 'closed', 'cerrado' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'in_progress', 'en progreso' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                        default => 'bg-slate-500/10 text-slate-400 border-slate-500/20'
                                    };
                                @endphp
                                <span class="status-badge {{ $sStyle }} border">
                                    {{ $ticket['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-2 text-[10px]">
                                        <span class="text-slate-500 font-bold uppercase w-16">Resuelto:</span>
                                        <span class="{{ $ticket['resolved_at'] ? 'text-emerald-400' : 'text-slate-600' }}">
                                            {{ $ticket['resolved_at'] ? \Carbon\Carbon::parse($ticket['resolved_at'])->format('d M, Y') : 'Pendiente' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px]">
                                        <span class="text-slate-500 font-bold uppercase w-16">Cerrado:</span>
                                        <span class="{{ $ticket['closed_at'] ? 'text-slate-300' : 'text-slate-600' }}">
                                            {{ $ticket['closed_at'] ? \Carbon\Carbon::parse($ticket['closed_at'])->format('d M, Y') : '—' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <a href="{{ route('support.ticket', $ticket['id']) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all shadow-inner">
                                    <span>→</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="text-slate-600 text-4xl mb-4 opacity-20">🎫</div>
                                <p class="text-slate-500 font-medium">No se encontraron tickets con los criterios seleccionados.</p>
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
                <div class="flex items-center gap-3">
                    <span class="text-indigo-400 font-bold">{{ $meta['total'] }} tickets en total</span>
                    <div class="flex gap-1">
                        <button class="px-3 py-1 bg-slate-800 rounded border border-slate-700 text-slate-500 hover:text-white transition-colors cursor-not-allowed">Anterior</button>
                        <button class="px-3 py-1 bg-slate-800 rounded border border-slate-700 text-slate-500 hover:text-white transition-colors cursor-not-allowed">Siguiente</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('ticketStatusChart').getContext('2d');
        
        // Data processing for the chart based on the provided array
        const tickets = @json($tickets);
        const statusMap = {};
        
        tickets.forEach(t => {
            const s = t.status.toLowerCase();
            statusMap[s] = (statusMap[s] || 0) + 1;
        });

        const labels = Object.keys(statusMap).map(s => s.charAt(0).toUpperCase() + s.slice(1));
        const data = Object.values(statusMap);

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#94a3b8',
                            font: { size: 10, weight: 'bold' },
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    });
</script>

@endsection