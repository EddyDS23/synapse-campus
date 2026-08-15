@extends('layouts.app')

@section('title', 'Horario - Synapse Campus')

@section('content')
<!-- Recursos para Gráficos y Estilo -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.tailwindcss.com"></script>

<style>
    body {
        background-color: #020617; /* Slate-950 */
        color: #f8fafc;
    }
    .glass-card {
        background: rgba(30, 41, 59, 0.5);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        border-color: #6366f1;
        transform: translateY(-2px);
    }
    .day-btn.active {
        background-color: #4f46e5;
        color: white;
        box-shadow: 0 0 20px rgba(79, 70, 229, 0.4);
    }
    .chart-container {
        position: relative;
        width: 100%;
        max-width: 400px;
        height: 200px;
        margin: 0 auto;
    }
    /* Scrollbar estilizada para modo oscuro */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #0f172a; }
    ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
</style>

<div class="max-w-7xl mx-auto p-6 lg:p-12 min-h-screen">
    
    <!-- Encabezado de Página -->
    <header class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-5xl font-black tracking-tighter text-white mb-2">Mi <span class="text-indigo-500">Horario</span></h1>
            <p class="text-slate-400 text-lg font-light">Gestiona tu agenda académica y distribución semanal.</p>
        </div>
        
        <div class="glass-card p-6 rounded-3xl flex items-center gap-6">
            <div class="hidden sm:block">
                <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Intensidad Semanal</p>
                <div class="chart-container">
                    <canvas id="weeklyLoadChart"></canvas>
                </div>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-white">{{ count($schedules) }}</p>
                <p class="text-xs text-slate-500 uppercase font-bold">Materias Totales</p>
            </div>
        </div>
    </header>

    @if (empty($schedules))
        <div class="glass-card p-20 rounded-[3rem] text-center">
            <p class="text-2xl text-slate-500 font-medium italic">No tienes clases asignadas en este ciclo.</p>
        </div>
    @else
        <!-- Selector de Días -->
        <div class="flex flex-wrap gap-3 mb-10" id="daySelector">
            @php $days = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado']; @endphp
            @foreach ($days as $day)
                <button onclick="filterDay('{{ $day }}')" 
                    class="day-btn px-8 py-3 rounded-2xl glass-card text-sm font-bold transition-all hover:bg-slate-800">
                    {{ $day }}
                </button>
            @endforeach
            <button onclick="filterDay('All')" 
                class="day-btn active px-8 py-3 rounded-2xl glass-card text-sm font-bold transition-all">
                Ver Todo
            </button>
        </div>

        <!-- Grid de Horario -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="scheduleGrid">
            @foreach ($schedules as $schedule)
                <div class="schedule-item glass-card p-8 rounded-[2rem] relative overflow-hidden group" data-day="{{ $schedule['day_of_week'] }}">
                    <!-- Decoración lateral de color segun el día -->
                    <div class="absolute left-0 top-0 bottom-0 w-2 bg-indigo-500 group-hover:w-3 transition-all"></div>
                    
                    <div class="flex justify-between items-start mb-6">
                        <span class="px-4 py-1 bg-indigo-500/10 text-indigo-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-500/20">
                            {{ $schedule['day_of_week'] }}
                        </span>
                        <div class="text-right">
                            <p class="text-xl font-bold text-white leading-none">
                                {{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }}
                            </p>
                            <p class="text-xs text-slate-500 font-medium uppercase mt-1">
                                a {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}
                            </p>
                        </div>
                    </div>

                    <h3 class="text-2xl font-extrabold text-white mb-2 leading-tight group-hover:text-indigo-400 transition-colors">
                        {{ $schedule['subject_name'] }}
                    </h3>
                    
                    <div class="space-y-4 mt-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400">
                                ◈
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-tighter">Profesor</p>
                                <p class="text-sm font-semibold text-slate-200">{{ $schedule['teacher'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400">
                                ◰
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-tighter">Grupo / Aula</p>
                                <p class="text-sm font-semibold text-slate-200">{{ $schedule['group'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-white/5 flex justify-between items-center">
                        <span class="text-xs text-slate-500 font-medium italic">Synapse Campus Academic Unit</span>
                        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    // Gráfico de Carga Semanal
    const ctx = document.getElementById('weeklyLoadChart').getContext('2d');
    
    // Contar clases por día para el gráfico
    const scheduleData = @json($schedules);
    const dayCounts = { 'Lunes': 0, 'Martes': 0, 'Miercoles': 0, 'Jueves': 0, 'Viernes': 0 };
    scheduleData.forEach(s => { if(dayCounts[s.day_of_week] !== undefined) dayCounts[s.day_of_week]++; });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['L', 'M', 'X', 'J', 'V'],
            datasets: [{
                data: Object.values(dayCounts),
                backgroundColor: '#6366f1',
                borderRadius: 4,
                barThickness: 12
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { 
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 10, weight: 'bold' } }
                }
            }
        }
    });

    // Función de Filtrado de Días
    function filterDay(day) {
        const items = document.querySelectorAll('.schedule-item');
        const buttons = document.querySelectorAll('.day-btn');
        
        // Actualizar botones
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if(btn.innerText === day || (day === 'All' && btn.innerText === 'Ver Todo')) {
                btn.classList.add('active');
            }
        });

        // Filtrar grid
        items.forEach(item => {
            if (day === 'All' || item.getAttribute('data-day') === day) {
                item.style.display = 'block';
                item.style.opacity = '0';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transition = 'opacity 0.4s ease';
                }, 10);
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Inicializar mostrando todo
    document.addEventListener('DOMContentLoaded', () => filterDay('All'));
</script>

@endsection