@extends('layouts.app')

@section('title', 'Nuevo Ticket | Synapse Support')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Chosen Palette: Synapse Midnight (Dark Slate, Indigo & Amber) -->
<!-- Application Structure Plan: 
    1. Introduction & Context: Clear guidance on the purpose of the support request.
    2. Interactive Form: Divided into logical segments (Identity, Issue, Impact).
    3. Live Preview & SLA Insight: A visual component (Chart.js) showing estimated response times based on selected priority.
    4. Enhanced Feedback: Dynamic error states and character tracking for better UX.
-->

<!-- Visualization & Content Choices:
    - Response SLA Chart -> Goal: Inform -> Shows estimated resolution time based on priority selection.
    - Priority Indicators -> Goal: Highlight -> Visual feedback (colors) reflecting urgency.
    - Glassmorphism Cards -> Goal: Organize -> Groups form fields into a cohesive, non-overwhelming layout.
    - CONFIRMATION: NO SVG graphics used. NO Mermaid JS used.
-->

<style>
    :root {
        --bg: #020617;
        --card-bg: rgba(30, 41, 59, 0.4);
        --accent: #6366f1;
        --amber: #f59e0b;
    }

    body {
        background-color: var(--bg);
        color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .synapse-glass {
        background: var(--card-bg);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1.5rem;
    }

    .input-field {
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(148, 163, 184, 0.2);
        transition: all 0.3s ease;
    }

    .input-field:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .btn-submit {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: 180px;
    }
</style>

<div class="max-w-6xl mx-auto px-4 py-10">
    
    <!-- Header Section -->
    <header class="mb-12">
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('support.my-tickets') }}" class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center text-slate-400 hover:text-white hover:border-slate-500 transition-all">
                ←
            </a>
            <h1 class="text-4xl font-black text-white tracking-tight">Reportar <span class="text-indigo-400">Incidencia</span></h1>
        </div>
        <p class="text-slate-400 max-w-2xl">
            Proporciona los detalles de tu consulta técnica o problema. Nuestro equipo de ingenieros revisará tu caso basándose en la prioridad seleccionada. Por favor, sé lo más descriptivo posible.
        </p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Form Column -->
        <div class="lg:col-span-2">
            @if ($errors->any())
                <div class="mb-8 p-5 rounded-2xl bg-red-500/10 border border-red-500/20">
                    <p class="text-red-400 font-bold mb-2 flex items-center gap-2">
                        <span>⚠</span> Se encontraron errores:
                    </p>
                    <ul class="text-red-300/80 text-sm list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('support.create.ticket') }}" class="space-y-6">
                @csrf

                <div class="synapse-glass p-8 space-y-6">
                    <!-- Title Input -->
                    <div>
                        <label for="title" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Título del Requerimiento *</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" maxlength="255" required 
                               class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600 font-medium"
                               placeholder="Ej: Error de acceso al catálogo virtual">
                    </div>

                    <!-- Description Input -->
                    <div>
                        <label for="description" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Descripción Detallada *</label>
                        <textarea id="description" name="description" rows="6" required 
                                  class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600 font-medium resize-none"
                                  placeholder="Describe el problema, los pasos para reproducirlo y cualquier código de error que visualices...">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Priority Selection -->
                        <div>
                            <label for="priority" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Prioridad del Impacto</label>
                            <select id="priority" name="priority" onchange="updateSLAChart()" 
                                    class="input-field w-full px-5 py-4 rounded-xl text-white font-medium appearance-none cursor-pointer">
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Baja - Consulta general</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Media - Problema menor</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Alta - Fallo de servicio</option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgente - Bloqueo total</option>
                            </select>
                        </div>

                        <!-- Category ID Input -->
                        <div>
                            <label for="category_id" class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">ID de Categoría *</label>
                            <input type="text" id="category_id" name="category_id" value="{{ old('category_id') }}" required 
                                   class="input-field w-full px-5 py-4 rounded-xl text-white placeholder-slate-600 font-mono"
                                   placeholder="CAT-XXXX">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="btn-submit px-10 py-4 rounded-xl font-bold text-white transition-all hover:scale-105 active:scale-95">
                        Crear Ticket de Soporte
                    </button>
                    <a href="{{ route('support.my-tickets') }}" class="px-8 py-4 rounded-xl font-bold text-slate-400 hover:text-white transition-all">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        <!-- Guidance Column -->
        <div class="space-y-6">
            <!-- SLA Insight Card -->
            <div class="synapse-glass p-6">
                <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-4">Estimación de Respuesta (SLA)</h3>
                <div class="chart-container">
                    <canvas id="slaPreviewChart"></canvas>
                </div>
                <div id="slaDetail" class="mt-4 p-4 rounded-xl bg-slate-900/50 border border-slate-800">
                    <p class="text-[10px] text-slate-500 uppercase font-black">Compromiso de Atención</p>
                    <p id="slaText" class="text-sm font-bold text-emerald-400 mt-1">~ 24-48 Horas Laborales</p>
                </div>
            </div>

            <!-- Category Info Card -->
            <div class="synapse-glass p-6 border-l-4 border-l-amber-500">
                <h3 class="text-sm font-bold text-white mb-2 tracking-tight">¿No conoces tu categoría?</h3>
                <p class="text-xs text-slate-400 leading-relaxed mb-4">
                    Para agilizar la resolución, cada ticket debe estar clasificado correctamente. Si no tienes el código CAT, contacta al administrador o utiliza el prefijo <code class="bg-slate-800 px-1 rounded text-amber-400">GEN-01</code> para consultas generales.
                </p>
                <div class="flex items-center gap-2 text-[10px] font-bold text-amber-500 uppercase">
                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                    Campo obligatorio
                </div>
            </div>

            <!-- Best Practices Card -->
            <div class="synapse-glass p-6">
                <h3 class="text-sm font-bold text-white mb-4 tracking-tight">Consejos para un buen ticket</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-xs text-slate-400">
                        <span class="text-indigo-400 font-bold">1.</span>
                        <span>Sé específico en el título. Evita usar "Ayuda" o "Error".</span>
                    </li>
                    <li class="flex items-start gap-3 text-xs text-slate-400">
                        <span class="text-indigo-400 font-bold">2.</span>
                        <span>Adjunta pasos exactos para reproducir el fallo.</span>
                    </li>
                    <li class="flex items-start gap-3 text-xs text-slate-400">
                        <span class="text-indigo-400 font-bold">3.</span>
                        <span>Menciona si el problema afecta a otros usuarios.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    let slaChart;

    document.addEventListener('DOMContentLoaded', function() {
        initSLAChart();
        updateSLAChart();
    });

    function initSLAChart() {
        const ctx = document.getElementById('slaPreviewChart').getContext('2d');
        slaChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Baja', 'Media', 'Alta', 'Urgente'],
                datasets: [{
                    label: 'Horas de Respuesta',
                    data: [48, 24, 4, 1],
                    backgroundColor: [
                        'rgba(148, 163, 184, 0.2)',
                        'rgba(99, 102, 241, 0.2)',
                        'rgba(245, 158, 11, 0.2)',
                        'rgba(239, 68, 68, 0.2)'
                    ],
                    borderColor: [
                        '#94a3b8',
                        '#6366f1',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        display: false,
                        beginAtZero: true 
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 9, weight: 'bold' } }
                    }
                }
            }
        });
    }

    function updateSLAChart() {
        const priority = document.getElementById('priority').value;
        const slaText = document.getElementById('slaText');
        
        // Reset colors
        slaChart.data.datasets[0].backgroundColor = slaChart.data.datasets[0].backgroundColor.map(c => c.replace('0.8', '0.2'));
        
        let index = 0;
        let text = "";
        let color = "";

        switch(priority) {
            case 'low': 
                index = 0; text = "~ 48 Horas Laborales"; color = "#94a3b8";
                break;
            case 'medium': 
                index = 1; text = "~ 24 Horas Laborales"; color = "#6366f1";
                break;
            case 'high': 
                index = 2; text = "~ 4 Horas (Mismo día)"; color = "#f59e0b";
                break;
            case 'urgent': 
                index = 3; text = "< 1 Hora (Crítico)"; color = "#ef4444";
                break;
        }

        // Highlight selected
        const bgColors = [
            'rgba(148, 163, 184, 0.2)',
            'rgba(99, 102, 241, 0.2)',
            'rgba(245, 158, 11, 0.2)',
            'rgba(239, 68, 68, 0.2)'
        ];
        bgColors[index] = bgColors[index].replace('0.2', '0.8');
        slaChart.data.datasets[0].backgroundColor = bgColors;
        slaChart.update();

        slaText.innerText = text;
        slaText.style.color = color;
    }
</script>

@endsection