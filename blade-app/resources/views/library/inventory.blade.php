@extends('layouts.app')

@section('title', 'Inventario | Synapse Library')

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
            <h1 class="text-4xl font-black text-white tracking-tight">Inventario de <span class="text-indigo-400">Libros</span></h1>
            <p class="text-slate-400 mt-1">Gestión del catálogo bibliográfico y existencias.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
            @if ($errors->any())
                <div class="w-full sm:w-64 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-xs font-bold animate-pulse">
                    ⚠️ {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="w-full sm:w-64 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl text-xs font-bold">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <button onclick="document.getElementById('modalCreate').style.display='flex'" class="btn-primary w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-bold text-white text-center shadow-lg flex items-center justify-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Agregar libro
            </button>
        </div>
    </header>

    <!-- Filter Bar -->
    <div class="synapse-glass p-6 mb-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="flex items-center gap-2 mb-2 lg:mb-0 min-w-max">
            <span class="w-2 h-2 bg-indigo-500 rounded-full animate-ping"></span>
            <h3 class="font-bold text-sm text-slate-300">Filtros de Catálogo</h3>
        </div>
        
        <form method="GET" action="{{ route('library.inventory') }}" class="flex flex-wrap items-center gap-4 w-full lg:justify-end">
            
            <div class="flex-grow sm:flex-grow-0">
                <input type="text" name="title" value="{{ request('title') }}" placeholder="Buscar por título..." class="w-full sm:w-60 bg-slate-950 border border-slate-800 text-xs rounded-lg px-4 py-2.5 text-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors">
            </div>

            <div class="flex-grow sm:flex-grow-0">
                <input type="text" name="category" value="{{ request('category') }}" placeholder="Categoría..." class="w-full sm:w-40 bg-slate-950 border border-slate-800 text-xs rounded-lg px-4 py-2.5 text-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors">
            </div>

            <div class="flex items-center gap-2 bg-slate-900/50 border border-slate-800 px-4 py-2.5 rounded-lg cursor-pointer hover:bg-slate-800/50 transition-colors">
                <input type="checkbox" id="available" name="available" value="1" {{ request('available') ? 'checked' : '' }} class="accent-indigo-500 w-4 h-4 cursor-pointer">
                <label for="available" class="text-xs font-bold text-slate-400 cursor-pointer select-none">Solo disponibles</label>
            </div>

            <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                <button type="submit" class="flex-1 sm:flex-none bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 px-5 py-2.5 rounded-lg text-xs font-bold hover:bg-indigo-600 hover:text-white transition-all">
                    Filtrar
                </button>
                <a href="{{ route('library.inventory') }}" class="flex-1 sm:flex-none text-center bg-slate-800 text-slate-400 px-5 py-2.5 rounded-lg text-xs font-bold hover:bg-slate-700 transition-all">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="synapse-glass overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-800 bg-slate-900/20">
                        <th class="px-6 py-5">Título del Libro</th>
                        <th class="px-6 py-5">Autor</th>
                        <th class="px-6 py-5">Categoría</th>
                        <th class="px-6 py-5">Inventario</th>
                        <th class="px-6 py-5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($books as $book)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-6 py-5">
                                <div class="font-bold text-white text-sm group-hover:text-indigo-300 transition-colors">{{ $book['title'] }}</div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">ID: {{ str_pad($book['id'], 4, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="px-6 py-5 text-sm text-slate-300 font-medium">
                                {{ $book['author'] }}
                            </td>
                            <td class="px-6 py-5">
                                <span class="bg-slate-800 text-slate-300 text-[10px] font-extrabold uppercase px-3 py-1 rounded-full border border-slate-700">
                                    {{ $book['category'] }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                @if(($book['stock_available'] ?? 0) > 0)
                                    <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-lg">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        <span class="text-emerald-400 font-bold text-xs">{{ $book['stock_available'] }} disp.</span>
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-2 bg-red-500/10 border border-red-500/20 px-3 py-1 rounded-lg">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                        <span class="text-red-400 font-bold text-xs">Agotado</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Botón Editar -->
                                    <button onclick="openEditModal('{{ $book['id'] }}','{{ addslashes($book['title']) }}','{{ addslashes($book['author']) }}','{{ addslashes($book['isbn'] ?? '') }}','{{ addslashes($book['category']) }}')" class="inline-flex items-center justify-center h-8 px-3 rounded-lg bg-slate-800 text-indigo-400 hover:bg-indigo-600 hover:text-white transition-all text-xs font-bold border border-slate-700">
                                        Editar
                                    </button>
                                    <!-- Botón Stock -->
                                    <button onclick="openStockModal('{{ $book['id'] }}','{{ addslashes($book['title']) }}')" class="inline-flex items-center justify-center h-8 px-3 rounded-lg bg-slate-800 text-amber-400 hover:bg-amber-500 hover:text-white transition-all text-xs font-bold border border-slate-700">
                                        Stock
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="text-slate-700 text-4xl mb-4 opacity-30">📚</div>
                                <p class="text-slate-400 font-medium text-sm">No se encontraron libros en el inventario.</p>
                                <a href="{{ route('library.inventory') }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-bold mt-2 inline-block">Restablecer filtros</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(!empty($books))
            <div class="p-6 bg-slate-900/40 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs">
                <p class="text-slate-500 font-medium">
                    Mostrando página <span class="text-white font-bold">{{ $meta['currentPage'] }}</span> de <span class="text-white font-bold">{{ $meta['totalPage'] }}</span>
                </p>
                <div class="flex items-center gap-3 bg-slate-950/50 px-4 py-2 rounded-lg border border-slate-800/50">
                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                    <span class="text-slate-300 font-bold">{{ $meta['total'] }} libros en total</span>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ══ MODAL CREAR ══ --}}
<div id="modalCreate" style="display:none;" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[100] items-center justify-center p-4">
    <div class="synapse-glass w-full max-w-md p-8 border-t-2 border-t-indigo-500 shadow-2xl relative">
        
        <button onclick="document.getElementById('modalCreate').style.display='none'" class="absolute top-6 right-6 text-slate-500 hover:text-white transition-colors">✕</button>
        
        <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">📘</span>
            <h2 class="text-xl font-black text-white">Agregar Libro</h2>
        </div>

        <form method="POST" action="{{ route('library.inventory.create') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Título *</label>
                <input type="text" name="title" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors text-sm">
            </div>
            <div>
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Autor *</label>
                <input type="text" name="author" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">ISBN *</label>
                    <input type="text" name="isbn" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors text-sm">
                </div>
                <div>
                    <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Categoría *</label>
                    <input type="text" name="category" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors text-sm">
                </div>
            </div>
            <div>
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Stock inicial *</label>
                <input type="number" name="stock_total" min="1" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors text-sm">
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('modalCreate').style.display='none'" class="flex-1 bg-slate-800 text-slate-300 font-bold py-3 rounded-xl hover:bg-slate-700 transition-colors text-sm">Cancelar</button>
                <button type="submit" class="flex-1 btn-primary text-white font-bold py-3 rounded-xl text-sm">Guardar Libro</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ MODAL EDITAR ══ --}}
<div id="modalEdit" style="display:none;" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[100] items-center justify-center p-4">
    <div class="synapse-glass w-full max-w-md p-8 border-t-2 border-t-indigo-500 shadow-2xl relative">
        
        <button onclick="document.getElementById('modalEdit').style.display='none'" class="absolute top-6 right-6 text-slate-500 hover:text-white transition-colors">✕</button>

        <div class="flex items-center gap-3 mb-6">
            <span class="text-2xl">✏️</span>
            <h2 class="text-xl font-black text-white">Editar Libro</h2>
        </div>

        <form method="POST" id="editForm" action="" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Título</label>
                <input type="text" name="title" id="editTitle" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors text-sm">
            </div>
            <div>
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Autor</label>
                <input type="text" name="author" id="editAuthor" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">ISBN</label>
                    <input type="text" name="isbn" id="editIsbn" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors text-sm">
                </div>
                <div>
                    <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Categoría</label>
                    <input type="text" name="category" id="editCategory" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-colors text-sm">
                </div>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('modalEdit').style.display='none'" class="flex-1 bg-slate-800 text-slate-300 font-bold py-3 rounded-xl hover:bg-slate-700 transition-colors text-sm">Cancelar</button>
                <button type="submit" class="flex-1 btn-primary text-white font-bold py-3 rounded-xl text-sm">Actualizar</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ MODAL STOCK ══ --}}
<div id="modalStock" style="display:none;" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-[100] items-center justify-center p-4">
    <div class="synapse-glass w-full max-w-sm p-8 border-t-2 border-t-amber-500 shadow-2xl relative">
        
        <button onclick="document.getElementById('modalStock').style.display='none'" class="absolute top-6 right-6 text-slate-500 hover:text-white transition-colors">✕</button>

        <div class="flex items-center gap-3 mb-2">
            <span class="text-2xl">📦</span>
            <h2 class="text-xl font-black text-white">Ajustar Stock</h2>
        </div>
        <p id="stockBookTitle" class="text-xs font-bold text-slate-400 mb-6 truncate"></p>

        <form method="POST" id="stockForm" action="" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">Ajuste de stock *</label>
                <input type="number" name="stock_total" required placeholder="Ej: 5 para agregar, -2 para reducir" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition-colors text-sm">
                <p class="text-[10px] text-slate-500 mt-2 font-medium">Usa valores positivos para agregar ejemplares y negativos para reducir el inventario.</p>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="document.getElementById('modalStock').style.display='none'" class="flex-1 bg-slate-800 text-slate-300 font-bold py-3 rounded-xl hover:bg-slate-700 transition-colors text-sm">Cancelar</button>
                <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-400 text-slate-900 font-black py-3 rounded-xl transition-colors text-sm">Aplicar Ajuste</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, title, author, isbn, category) {
    document.getElementById('editForm').action = '/library/books/' + id;
    document.getElementById('editTitle').value    = title;
    document.getElementById('editAuthor').value   = author;
    document.getElementById('editIsbn').value     = isbn;
    document.getElementById('editCategory').value = category;
    document.getElementById('modalEdit').style.display = 'flex';
}

function openStockModal(id, title) {
    document.getElementById('stockForm').action = '/library/books/' + id + '/stock';
    document.getElementById('stockBookTitle').textContent = title;
    document.getElementById('modalStock').style.display = 'flex';
}

// Cerrar modales con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('modalCreate').style.display = 'none';
        document.getElementById('modalEdit').style.display   = 'none';
        document.getElementById('modalStock').style.display  = 'none';
    }
});
</script>
@endsection