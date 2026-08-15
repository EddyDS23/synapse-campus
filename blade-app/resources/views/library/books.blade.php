@extends('layouts.app')

@section('title', 'Catálogo de Libros | Synapse')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    body {
        background-color: #020617; /* Slate 950 */
        color: #f1f5f9;
    }
    .synapse-card {
        background: rgba(30, 41, 59, 0.5); /* Slate 800 con opacidad */
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1.25rem;
        transition: all 0.3s ease;
    }
    .synapse-card:hover {
        border-color: #6366f1;
        background: rgba(30, 41, 59, 0.8);
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
    }
    .chart-container {
        position: relative;
        width: 100%;
        max-width: 100%;
        height: 200px;
        margin: 0 auto;
    }
    .input-premium {
        height: 3rem;
        padding: 0 1rem;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 0.75rem;
        color: #f8fafc;
        transition: all 0.2s;
    }
    .input-premium:focus {
        outline: none;
        border-color: #6366f1;
        background: #1e293b;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
    .btn-loan {
        background: #6366f1;
        color: white;
        font-weight: 700;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        transition: all 0.2s;
        width: 100%;
    }
    .btn-loan:hover {
        background: #4f46e5;
        transform: translateY(-1px);
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
    }
    .btn-disabled {
        background: #1e293b;
        color: #64748b;
        cursor: not-allowed;
        font-weight: 700;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        width: 100%;
        border: 1px solid #334155;
    }
</style>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <!-- Encabezado con Feedback -->
    <header class="mb-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tight">Catálogo de <span class="text-indigo-400">Libros</span></h1>
                <p class="text-slate-400 mt-1">Explora la base de conocimientos Synapse en modo de alta concentración.</p>
            </div>
            
            <div class="flex flex-col gap-2 min-w-[300px]">
                @if ($errors->any())
                    <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
                        <span>⚠</span> {{ $errors->first('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
                        <span>✓</span> {{ session('success') }}
                    </div>
                @endif
            </div>
        </div>
    </header>

    <!-- Sección de Analítica Nocturna -->
    @if(!empty($books))
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="synapse-card p-6 flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-4">Distribución Temática</h3>
            </div>
            <div class="chart-container">
                <canvas id="categoryDistributionChart"></canvas>
            </div>
        </div>
        
        <div class="lg:col-span-2 synapse-card p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Inventario en Tiempo Real</h3>
                    <p class="text-2xl font-black text-white">Disponibilidad de Stock</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-500 uppercase">Libros Cargados</p>
                    <p class="text-3xl font-black text-indigo-400">{{ $meta['total'] ?? 0 }}</p>
                </div>
            </div>
            <div class="chart-container h-[140px]">
                <canvas id="stockVolumeChart"></canvas>
            </div>
        </div>
    </section>
    @endif

    <!-- Barra de Filtros Oscura -->
    <section class="synapse-card p-4 mb-8 bg-slate-900/40">
        <form method="GET" action="{{ route('library.books') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-slate-500 uppercase ml-2 mb-1">Título</label>
                <input type="text" name="title" placeholder="Buscar título..." value="{{ request('title') }}" class="input-premium w-full">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-slate-500 uppercase ml-2 mb-1">Autor</label>
                <input type="text" name="autor" placeholder="Nombre del autor..." value="{{ request('autor') }}" class="input-premium w-full">
            </div>
            <div class="w-full md:w-48">
                <label class="block text-[10px] font-bold text-slate-500 uppercase ml-2 mb-1">Categoría</label>
                <input type="text" name="category" placeholder="Filtrar tema" value="{{ request('category') }}" class="input-premium w-full">
            </div>
            <div class="flex items-center gap-2 mb-3 px-2">
                <input type="checkbox" name="available" value="1" id="available" {{ request('available') ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-700 bg-slate-800 text-indigo-500 focus:ring-indigo-600 focus:ring-offset-slate-900">
                <label for="available" class="text-sm font-bold text-slate-400 cursor-pointer">Solo disponibles</label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="h-12 px-8 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-500 transition-all flex items-center gap-2 shadow-lg shadow-indigo-900/20">
                    <span>🔍</span> Buscar
                </button>
                <a href="{{ route('library.books') }}" class="h-12 px-6 bg-slate-800 text-slate-300 border border-slate-700 rounded-xl font-bold hover:text-white hover:bg-slate-700 transition-all flex items-center">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <!-- Grid de Resultados -->
    @if(empty($books))
        <div class="synapse-card py-20 text-center border-dashed border-2 border-slate-800">
            <div class="text-5xl mb-4 opacity-20">📖</div>
            <h3 class="text-xl font-bold text-slate-300">Sin coincidencias</h3>
            <p class="text-slate-500">Ajusta los filtros para encontrar lo que buscas.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($books as $book)
                <div class="synapse-card p-6 flex flex-col group">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-10 h-14 bg-indigo-500/10 border border-indigo-500/20 rounded flex flex-col items-center justify-center text-indigo-400 font-bold text-[10px]">
                            LIB
                            <div class="w-6 h-px bg-indigo-500/30 my-1"></div>
                            {{ $loop->iteration }}
                        </div>
                        @if($book['stock_available'] > 0)
                            <span class="bg-emerald-500/10 text-emerald-400 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-tighter border border-emerald-500/20">
                                {{ $book['stock_available'] }} Disponibles
                            </span>
                        @else
                            <span class="bg-red-500/10 text-red-400 text-[10px] font-black px-2 py-1 rounded-full uppercase tracking-tighter border border-red-500/20">
                                Agotado
                            </span>
                        @endif
                    </div>

                    <div class="mb-6 flex-grow">
                        <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">🏷️ {{ $book['category'] }}</p>
                        <h4 class="text-lg font-extrabold text-white leading-tight mb-2 group-hover:text-indigo-300 transition-colors">{{ $book['title'] }}</h4>
                        <p class="text-sm font-medium text-slate-400">👤 {{ $book['author'] }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-700/50">
                        @if($book['stock_available'] > 0)
                            <form method="POST" action="{{ route('library.loan.request', $book['id']) }}">
                                @csrf
                                <button type="submit" class="btn-loan flex items-center justify-center gap-2">
                                    <span>Solicitar Préstamo</span>
                                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                                </button>
                            </form>
                        @else
                            <button class="btn-disabled" disabled>No Disponible</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginación Nocturna -->
        <div class="mt-12 flex flex-col md:flex-row items-center justify-between gap-6 border-t border-slate-800 pt-8">
            <div class="text-sm font-medium text-slate-500">
                Página <span class="text-white font-bold">{{ $meta['currentPage'] }}</span> de <span class="text-white font-bold">{{ $meta['totalPage'] }}</span>
                <span class="mx-2 text-slate-700">|</span>
                Total: <span class="text-indigo-400 font-black">{{ $meta['total'] }}</span> resultados
            </div>
            
            <div class="flex gap-2">
                <button class="w-10 h-10 rounded-xl border border-slate-800 flex items-center justify-center hover:bg-slate-800 text-slate-500 transition-all">←</button>
                <div class="flex gap-1">
                    @for($i = 1; $i <= min($meta['totalPage'], 5); $i++)
                        <button class="w-10 h-10 rounded-xl {{ $i == $meta['currentPage'] ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/40' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:bg-slate-800' }} font-bold transition-all">
                            {{ $i }}
                        </button>
                    @endfor
                </div>
                <button class="w-10 h-10 rounded-xl border border-slate-800 flex items-center justify-center hover:bg-slate-800 text-slate-500 transition-all">→</button>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Colores para el tema oscuro
        const chartText = '#94a3b8';
        const chartGrid = 'rgba(255, 255, 255, 0.05)';

        const books = @json($books);
        
        if (books && books.length > 0) {
            // 1. Gráfico de Categorías
            const categoryCounts = {};
            books.forEach(b => {
                categoryCounts[b.category] = (categoryCounts[b.category] || 0) + 1;
            });

            const catCtx = document.getElementById('categoryDistributionChart').getContext('2d');
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(categoryCounts),
                    datasets: [{
                        data: Object.values(categoryCounts),
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'],
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
                                usePointStyle: true, 
                                color: chartText,
                                font: { size: 10, weight: '600' },
                                padding: 15 
                            }
                        }
                    }
                }
            });

            // 2. Gráfico de Stock
            const stockCtx = document.getElementById('stockVolumeChart').getContext('2d');
            const bookTitles = books.slice(0, 8).map(b => b.title.length > 15 ? b.title.substring(0, 15) + '...' : b.title);
            const stockValues = books.slice(0, 8).map(b => b.stock_available);

            new Chart(stockCtx, {
                type: 'bar',
                data: {
                    labels: bookTitles,
                    datasets: [{
                        label: 'Stock',
                        data: stockValues,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        hoverBackgroundColor: '#6366f1',
                        borderRadius: 6,
                        barThickness: 20
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: chartGrid }, 
                            ticks: { color: chartText, font: { size: 9 } } 
                        },
                        x: { 
                            grid: { display: false }, 
                            ticks: { color: chartText, font: { size: 9 } } 
                        }
                    }
                }
            });
        }
    });
</script>
@endsection