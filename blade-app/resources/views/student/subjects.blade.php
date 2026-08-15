@extends('layouts.app')

@section('title', 'Materias - Mapa Curricular | Synapse Campus')

@section('content')
<!-- Fuentes y Estilos para Modo Nocturno -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.tailwindcss.com"></script>

<style>
    body {
        background-color: #020617; /* Slate-950 */
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #f8fafc;
    }
    .glass-card {
        background: rgba(30, 41, 59, 0.5);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .semester-btn.active {
        background: linear-gradient(to right, #6366f1, #a855f7);
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
        border-color: transparent;
    }
    .subject-node {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .subject-node:hover {
        transform: scale(1.02) translateY(-5px);
        border-color: #22d3ee;
        box-shadow: 0 10px 30px -10px rgba(34, 211, 238, 0.3);
    }
</style>

<div class="max-w-[1600px] mx-auto p-4 md:p-10">
    
    <!-- Cabecera: Analítica de Carrera -->
    <header class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <div class="lg:col-span-2 glass-card rounded-[2.5rem] p-8 flex flex-col md:flex-row items-center gap-10 shadow-lg">
            <div class="relative w-48 h-48 flex-shrink-0">
                <canvas id="careerProgressChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-4xl font-black text-white">74%</span>
                    <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Avance</span>
                </div>
            </div>
            <div class="flex-grow text-center md:text-left">
                <h1 class="text-4xl font-extrabold tracking-tighter mb-2 text-white">Mapa Curricular</h1>
                <p class="text-slate-400 text-lg font-light mb-6">Explora tu trayectoria académica y planifica tus próximos créditos.</p>
                <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                    <div class="bg-slate-900/50 px-6 py-3 rounded-2xl border border-slate-700/50">
                        <p class="text-xs text-slate-500 font-bold uppercase mb-1">Total Materias</p>
                        <p class="text-2xl font-bold text-white">54</p>
                    </div>
                    <div class="bg-slate-900/50 px-6 py-3 rounded-2xl border border-slate-700/50">
                        <p class="text-xs text-slate-500 font-bold uppercase mb-1">Créditos Totales</p>
                        <p class="text-2xl font-bold text-cyan-400">320</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-[2.5rem] p-8 hidden lg:block shadow-lg">
            <p class="text-[10px] font-black uppercase text-indigo-400 mb-4 tracking-widest text-center">Distribución de Conocimiento</p>
            <div class="h-40">
                <canvas id="knowledgeChart"></canvas>
            </div>
        </div>
    </header>

    @if(empty($career_subjects))
        <div class="glass-card rounded-3xl p-20 text-center border border-slate-800">
            <div class="text-5xl mb-4 opacity-20">🌌</div>
            <p class="text-2xl text-slate-500 font-bold">No hay registros en el mapa estelar académico.</p>
        </div>
    @else
        
        <!-- Selector Móvil: Desplegable Flotante (Solo visible en pantallas pequeñas) -->
        <div class="block lg:hidden sticky top-4 z-50 mb-8">
            <div class="relative">
                <select id="mobile-semester-select" onchange="showSemester(this.value)" class="w-full bg-slate-900/90 backdrop-blur-xl border border-indigo-500/30 text-white rounded-2xl px-5 py-4 font-bold outline-none focus:border-indigo-500 appearance-none shadow-2xl shadow-indigo-500/10 transition-colors">
                    @foreach($career_subjects as $index => $semester)
                        <option value="{{ $semester['semester'] }}" class="bg-slate-900 text-slate-200">
                            Semestre Nivel {{ sprintf("%02d", $semester['semester']) }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-indigo-400 font-bold">
                    ▼
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 relative">
            
            <!-- Navegación de Escritorio: Sidebar Sticky (Solo visible en pantallas grandes) -->
            <aside class="hidden lg:flex lg:flex-col lg:w-72 gap-3 sticky top-8 h-fit z-40">
                @foreach($career_subjects as $index => $semester)
                    <button onclick="showSemester({{ $semester['semester'] }})" 
                        id="btn-sem-{{ $semester['semester'] }}"
                        class="semester-btn w-full text-left px-6 py-5 rounded-2xl border border-slate-700/50 hover:border-slate-500 transition-all group {{ $index == 0 ? 'active' : 'bg-slate-900/40' }}">
                        <span class="block text-[10px] font-black uppercase text-slate-500 group-hover:text-slate-300 transition-colors mb-1">Semestre</span>
                        <span class="text-xl font-bold tracking-tight text-white">Nivel {{ sprintf("%02d", $semester['semester']) }}</span>
                    </button>
                @endforeach
            </aside>

            <!-- Grid de Materias Dinámico -->
            <main class="flex-grow min-h-screen">
                @foreach($career_subjects as $index => $semester)
                    <div id="semester-grid-{{ $semester['semester'] }}" 
                        class="semester-content grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 {{ $index == 0 ? '' : 'hidden' }}">
                        
                        @foreach($semester['subjects'] as $subject)
                            <div class="subject-node glass-card p-6 rounded-3xl border border-slate-800 border-l-4 border-l-indigo-500 hover:border-l-cyan-400 bg-slate-900/30 flex flex-col">
                                <div class="flex justify-between items-start mb-4">
                                    <span class="bg-indigo-500/10 text-indigo-400 text-[10px] font-black px-2.5 py-1 rounded-md uppercase tracking-widest border border-indigo-500/20">
                                        {{ $subject['code'] }}
                                    </span>
                                    <div class="flex items-center gap-1 text-cyan-400 bg-cyan-500/10 px-2.5 py-1 rounded-md border border-cyan-500/20">
                                        <span class="text-xs font-bold">{{ $subject['credits'] }}</span>
                                        <span class="text-[8px] font-black uppercase tracking-widest">CRTS</span>
                                    </div>
                                </div>
                                
                                <h4 class="text-base md:text-lg font-bold text-white leading-snug mb-6 flex-grow">{{ $subject['name'] }}</h4>
                                
                                <div class="pt-4 border-t border-slate-800/80 flex justify-between items-center mt-auto">
                                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Obligatoria</span>
                                    <button class="text-[11px] font-bold text-indigo-400 hover:text-indigo-300 transition-colors bg-indigo-500/10 hover:bg-indigo-500/20 px-3 py-1.5 rounded-lg">
                                        Ver Detalles →
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        
                    </div>
                @endforeach
            </main>
        </div>
    @endif
</div>

<script>
    // Lógica de Navegación de Semestres unificada (Sincroniza Móvil y Escritorio)
    function showSemester(num) {
        // Ocultar todas las cuadrículas
        document.querySelectorAll('.semester-content').forEach(el => el.classList.add('hidden'));
        
        // Quitar la clase activa de todos los botones de escritorio
        document.querySelectorAll('.semester-btn').forEach(el => {
            el.classList.remove('active');
            el.classList.add('bg-slate-900/40'); // Restaurar fondo inactivo
        });
        
        // Mostrar la cuadrícula seleccionada
        const targetGrid = document.getElementById('semester-grid-' + num);
        if(targetGrid) targetGrid.classList.remove('hidden');
        
        // Activar el botón de escritorio correspondiente
        const desktopBtn = document.getElementById('btn-sem-' + num);
        if(desktopBtn) {
            desktopBtn.classList.add('active');
            desktopBtn.classList.remove('bg-slate-900/40');
        }
        
        // Sincronizar el select móvil si el cambio se hizo desde el escritorio
        const mobileSelect = document.getElementById('mobile-semester-select');
        if(mobileSelect && mobileSelect.value !== String(num)) {
            mobileSelect.value = num;
        }

        // En pantallas pequeñas, hacer un pequeño scroll hacia arriba si estaban muy abajo
        if (window.innerWidth < 1024) {
            window.scrollTo({
                top: targetGrid.offsetTop - 120, // Compensa el select sticky
                behavior: 'smooth'
            });
        }
    }

    // Inicialización de Gráficos (Adaptados al tema nocturno)
    document.addEventListener('DOMContentLoaded', function() {
        const progressChartEl = document.getElementById('careerProgressChart');
        if(progressChartEl) {
            new Chart(progressChartEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [74, 26],
                        backgroundColor: ['#6366f1', '#0f172a'], // Indigo 500 y Slate 900
                        borderWidth: 0,
                        borderRadius: 10,
                        cutout: '85%'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } } }
            });
        }

        const knowledgeChartEl = document.getElementById('knowledgeChart');
        if(knowledgeChartEl) {
            new Chart(knowledgeChartEl.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: ['Ciencias', 'Técnica', 'Sociales', 'Idiomas', 'Labs'],
                    datasets: [{
                        label: 'Áreas',
                        data: [85, 90, 60, 70, 95],
                        fill: true,
                        backgroundColor: 'rgba(34, 211, 238, 0.15)',
                        borderColor: '#22d3ee', // Cyan 400
                        borderWidth: 2,
                        pointBackgroundColor: '#020617',
                        pointBorderColor: '#22d3ee',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        r: {
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            angleLines: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { display: false },
                            pointLabels: { color: '#94a3b8', font: { weight: '800', size: 10 } } // Slate 400
                        }
                    }
                }
            });
        }
    });
</script>
@endsection