@extends('layouts.app')

@section('title', 'Dashboard - Synapse Campus')

@section('content')
<!-- Importación de recursos para el acabado premium -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.tailwindcss.com"></script>

<style>
    :root {
        --bg: #020617;
        --card-bg: rgba(30, 41, 59, 0.5);
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
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .synapse-glass:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        border-color: rgba(99, 102, 241, 0.3); /* Borde sutil índigo al hacer hover */
    }

    .chart-container {
        position: relative;
        width: 100%;
        max-width: 100%;
        height: 240px;
        margin: 0 auto;
    }

    .action-button {
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    
    .action-button::after {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: 0.5s;
    }
    
    .action-button:hover::after {
        left: 100%;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }
</style>

<div class="max-w-[1400px] mx-auto p-6 lg:p-10">
    
    <!-- Hero Banner: Bienvenida Institucional -->
    <header class="mb-10 relative group">
        <div class="bg-indigo-950/40 border border-indigo-500/20 backdrop-blur-xl rounded-[2rem] p-8 md:p-12 text-white flex flex-col md:flex-row justify-between items-center relative overflow-hidden shadow-2xl">
            <!-- Decoración de fondo con Canvas/CSS -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-indigo-600/10 rounded-full -ml-10 -mb-10 blur-2xl"></div>
            
            <div class="relative z-10 text-center md:text-left">
                <span class="inline-block px-4 py-1.5 bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 rounded-full text-[10px] font-black uppercase tracking-widest mb-4">
                    Portal Institucional Activo
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-2">
                    Hola, <span class="text-indigo-400">{{ explode('@', session('email'))[0] }}</span>
                </h1>
                <p class="text-slate-400 text-lg font-medium max-w-md">
                    Gestiona tu vida académica con la potencia de la inteligencia Synapse.
                </p>
            </div>
            
            <div class="mt-8 md:mt-0 relative z-10">
                <div class="bg-slate-900/60 border border-slate-700/50 p-6 rounded-3xl text-center shadow-inner">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Hoy es</p>
                    <p class="text-2xl font-black text-white">{{ now()->translatedFormat('d \d\e F') }}</p>
                    <p class="text-xs font-mono text-indigo-400 mt-1">{{ now()->format('Y') }}</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        @if (RoleHelper::hasRole('student'))
            <!-- Módulo: Portal Académico (Grande) -->
            <div class="lg:col-span-8 synapse-glass p-8 flex flex-col">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h3 class="text-2xl font-black text-white tracking-tight">Rendimiento Académico</h3>
                        <p class="text-xs font-medium text-slate-400 mt-1">Progreso acumulado del semestre actual</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="/profile" class="action-button btn-primary text-white px-6 py-3 rounded-xl text-sm font-bold shadow-lg">
                            Perfil Alumno
                        </a>
                        <a href="/schedule" class="action-button bg-slate-800 text-slate-300 border border-slate-700 px-6 py-3 rounded-xl text-sm font-bold hover:bg-slate-700 hover:text-white">
                            Horario
                        </a>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Módulo: Biblioteca (Vertical) -->
            <div class="lg:col-span-4 synapse-glass flex flex-col p-0">
                <div class="p-8 border-b border-slate-800/50">
                    <div class="flex items-center gap-3 mb-1">
                        <span class="text-xl">📚</span>
                        <h3 class="text-xl font-black text-white tracking-tight">Biblioteca Virtual</h3>
                    </div>
                    <p class="text-xs font-medium text-slate-400">Recursos y préstamos</p>
                </div>
                
                <div class="p-8 flex-grow space-y-4">
                    <a href="/library/loans" class="group flex items-center justify-between p-4 rounded-2xl bg-slate-900/40 border border-slate-800 hover:border-teal-500/50 transition-all">
                        <span class="font-bold text-slate-300 group-hover:text-teal-400 text-sm">Mis Préstamos</span>
                        <span class="bg-teal-500/10 border border-teal-500/20 text-teal-400 text-[10px] font-extrabold uppercase px-3 py-1 rounded-full">3 Activos</span>
                    </a>
                    
                    <a href="/library/fines" class="group flex items-center justify-between p-4 rounded-2xl bg-slate-900/40 border border-slate-800 hover:border-red-500/50 transition-all">
                        <span class="font-bold text-slate-300 group-hover:text-red-400 text-sm">Multas</span>
                        <span class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-extrabold uppercase px-3 py-1 rounded-full">Ninguna</span>
                    </a>
                </div>
                
                <div class="p-6 bg-slate-900/30 text-center border-t border-slate-800/50 mt-auto">
                    <p class="text-[10px] font-medium text-slate-500 uppercase tracking-widest">Consulta el reglamento aquí</p>
                </div>
            </div>

            <!-- Módulo: Soporte (Ancho) -->
            <div class="lg:col-span-12 synapse-glass p-8 border-l-4 border-l-indigo-500">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="flex-grow">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-2xl">🎧</span>
                            <h3 class="text-2xl font-black text-white tracking-tight">Centro de Ayuda y Soporte</h3>
                        </div>
                        <p class="text-sm font-medium text-slate-400">¿Tienes problemas técnicos o dudas académicas? Nuestro equipo está listo para ayudarte.</p>
                    </div>
                    <div class="flex gap-4 w-full md:w-auto">
                        <a href="/support/tickets" class="flex-1 md:flex-none text-center px-8 py-3.5 bg-slate-800 text-slate-300 border border-slate-700 rounded-xl text-sm font-bold hover:bg-slate-700 hover:text-white transition-colors">
                            Mis Tickets
                        </a>
                        <a href="/support/tickets/new" class="flex-1 md:flex-none text-center px-8 py-3.5 btn-primary text-white rounded-xl text-sm font-bold shadow-lg">
                            + Abrir Ticket
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if (RoleHelper::isAgent())
            <!-- Módulo Agente: Mesa de Ayuda -->
            <div class="lg:col-span-12 synapse-glass p-8 border-t-2 border-t-indigo-500">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
                    <div>
                        <h3 class="text-3xl font-black text-white tracking-tight">Consola de Agente</h3>
                        <p class="text-sm font-medium text-slate-400 mt-1">Mesa de ayuda Synapse</p>
                    </div>
                    <a href="/soporte/tickets" class="w-full md:w-auto text-center px-8 py-3.5 bg-slate-800 text-indigo-300 border border-slate-700 rounded-xl text-sm font-bold hover:bg-indigo-600 hover:text-white hover:border-indigo-500 transition-colors shadow-sm">
                        Gestionar Todos los Tickets
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-6 bg-slate-900/50 rounded-2xl border border-slate-800 text-center hover:border-indigo-500/30 transition-colors">
                        <p class="text-indigo-400 text-[10px] font-bold uppercase tracking-widest mb-2">Pendientes</p>
                        <p class="text-4xl font-black text-white">12</p>
                    </div>
                    <div class="p-6 bg-slate-900/50 rounded-2xl border border-slate-800 text-center hover:border-emerald-500/30 transition-colors">
                        <p class="text-emerald-400 text-[10px] font-bold uppercase tracking-widest mb-2">Resueltos Hoy</p>
                        <p class="text-4xl font-black text-white">45</p>
                    </div>
                    <div class="p-6 bg-slate-900/50 rounded-2xl border border-slate-800 text-center hover:border-red-500/30 transition-colors">
                        <p class="text-red-400 text-[10px] font-bold uppercase tracking-widest mb-2">SLA Crítico</p>
                        <p class="text-4xl font-black text-white animate-pulse">2</p>
                    </div>
                </div>
            </div>
        @endif

        @if (RoleHelper::isLibrarian())
            <!-- Módulo Bibliotecario: Inventario -->
            <div class="lg:col-span-12 synapse-glass p-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-teal-500/10 border border-teal-500/20 rounded-2xl flex items-center justify-center text-2xl font-bold">
                            📖
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-white tracking-tight">Gestión de Acervos</h3>
                            <p class="text-sm font-medium text-slate-400 mt-1">Administración de inventario y préstamos globales.</p>
                        </div>
                    </div>
                    <a href="/biblioteca/inventario" class="w-full md:w-auto px-8 py-3.5 bg-teal-500/10 text-teal-400 border border-teal-500/20 rounded-xl text-sm font-bold hover:bg-teal-500 hover:text-white transition-colors text-center">
                        Entrar al Inventario
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
    // Inicialización del gráfico de rendimiento adaptado al modo oscuro
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        // Gradiente ajustado para modo nocturno
        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.4)'); // indigo-500 más visible
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Semana 1', 'Semana 2', 'Semana 3', 'Semana 4', 'Semana 5', 'Semana 6', 'Semana 7', 'Semana 8'],
                datasets: [{
                    label: 'Promedio de Calificaciones',
                    data: [82, 85, 84, 88, 92, 90, 94, 96],
                    borderColor: '#6366f1', // indigo-500
                    borderWidth: 4,
                    pointBackgroundColor: '#0f172a', // slate-900 (coincide con el fondo oscuro)
                    pointBorderColor: '#818cf8', // indigo-400
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: gradient
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)', // slate-900
                        titleColor: '#cbd5e1', // slate-300
                        bodyColor: '#ffffff',
                        borderColor: 'rgba(99, 102, 241, 0.3)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 70,
                        grid: { 
                            color: 'rgba(255, 255, 255, 0.05)', // Líneas súper tenues
                            drawBorder: false
                        },
                        ticks: { font: { weight: 'bold', size: 11 }, color: '#64748b' } // slate-500
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 'bold', size: 11 }, color: '#64748b' }
                    }
                }
            }
        });
    });
</script>
@endsection