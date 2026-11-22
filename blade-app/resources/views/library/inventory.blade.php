@extends('layouts.app')

@section('title', 'Inventario | Synapse Library')

@section('content')
<x-page>
    <header class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">Inventario de <span class="text-[var(--color-primary)]">Libros</span></h1>
            <p class="text-[var(--color-muted-foreground)] mt-1">Gestión del catálogo bibliográfico y existencias.</p>
        </div>
        <x-button onclick="openCreateModal()" variant="primary" size="sm">+ Agregar Libro</x-button>
    </header>

    @if ($errors->any())
        <x-alert type="error" class="mb-4">{{ $errors->first() }}</x-alert>
    @endif
    @if (session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif

    <!-- Filtros -->
    <section class="mb-6">
        <x-card padding="p-4">
            <form method="GET" action="{{ route('library.inventory') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-bold text-[var(--color-muted-foreground)] uppercase mb-1">Título</label>
                    <input type="text" name="title" value="{{ request('title') }}" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-bold text-[var(--color-muted-foreground)] uppercase mb-1">Autor</label>
                    <input type="text" name="autor" value="{{ request('autor') }}" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </div>
                <div class="w-full md:w-48">
                    <label class="block text-[10px] font-bold text-[var(--color-muted-foreground)] uppercase mb-1">Categoría</label>
                    <input type="text" name="category" value="{{ request('category') }}" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </div>
                <div class="flex items-center gap-2 mb-1">
                    <input type="checkbox" name="available" value="1" id="inv-available" {{ request('available') ? 'checked' : '' }} class="w-4 h-4 rounded border-[var(--color-border)] text-[var(--color-primary)]">
                    <label for="inv-available" class="text-sm text-[var(--color-muted-foreground)] cursor-pointer">Solo disponibles</label>
                </div>
                <div class="flex gap-2">
                    <x-button type="submit" variant="primary" size="sm">Filtrar</x-button>
                    <a href="{{ route('library.inventory') }}" class="inline-flex items-center justify-center h-9 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] text-sm hover:bg-[var(--color-muted)]">Limpiar</a>
                </div>
            </form>
        </x-card>
    </section>

    <!-- Tabla -->
    <x-card padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-[var(--color-muted-foreground)] uppercase tracking-widest border-b border-[var(--color-border)]">
                        <th class="px-6 py-4">Título</th>
                        <th class="px-6 py-4">Autor</th>
                        <th class="px-6 py-4">Categoría</th>
                        <th class="px-6 py-4 text-center">Stock</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)]">
                    @forelse($books as $book)
                        <tr class="hover:bg-[var(--color-muted)]/40 transition-colors">
                            <td class="px-6 py-4 font-medium text-[var(--color-foreground)]">{{ $book['title'] }}</td>
                            <td class="px-6 py-4 text-[var(--color-muted-foreground)]">{{ $book['author'] }}</td>
                            <td class="px-6 py-4 text-[var(--color-muted-foreground)]">{{ $book['category'] }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-[var(--color-foreground)]">{{ $book['stock_available'] ?? 0 }}</span>
                                <span class="text-xs text-[var(--color-muted-foreground)]">/ {{ $book['stock_total'] ?? 0 }}</span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="openEditModal('{{ $book['id'] }}', '{{ addslashes($book['title']) }}', '{{ addslashes($book['author']) }}', '{{ addslashes($book['isbn'] ?? '') }}', '{{ addslashes($book['category']) }}')" class="text-[var(--color-primary)] hover:underline text-sm">Editar</button>
                                <button onclick="openStockModal('{{ $book['id'] }}', '{{ addslashes($book['title']) }}')" class="text-[var(--color-accent)] hover:underline text-sm">Stock</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-[var(--color-muted-foreground)]">No hay libros en el inventario.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(!empty($books) && isset($meta['totalPage']) && $meta['totalPage'] > 1)
            <div class="p-4 border-t border-[var(--color-border)] text-sm text-[var(--color-muted-foreground)]">
                Página {{ $meta['currentPage'] ?? 1 }} de {{ $meta['totalPage'] ?? 1 }} — {{ $meta['total'] ?? 0 }} resultados
            </div>
        @endif
    </x-card>

    <!-- Modales (crear/editar/stock) - simplificados -->
    <div id="modalCreate" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="modalCreateTitle">
        <div class="w-full max-w-lg rounded-[var(--radius-xl)] border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)]">
            <h2 id="modalCreateTitle" class="mb-4 text-lg font-semibold font-[family-name:var(--font-serif)]">Agregar Libro</h2>
            <form method="POST" action="{{ route('library.inventory.create') }}" class="space-y-3">
                @csrf
                <x-field label="Título"><input type="text" name="title" required class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <x-field label="Autor"><input type="text" name="author" required class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <x-field label="ISBN"><input type="text" name="isbn" required class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <x-field label="Categoría"><input type="text" name="category" required class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <x-field label="Stock total"><input type="number" name="stock_total" min="1" required class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <div class="flex justify-end gap-2 pt-2">
                    <x-button type="button" variant="ghost" size="sm" onclick="closeModals()">Cancelar</x-button>
                    <x-button type="submit" variant="primary" size="sm">Guardar</x-button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true">
        <div class="w-full max-w-lg rounded-[var(--radius-xl)] border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)]">
            <h2 class="mb-4 text-lg font-semibold font-[family-name:var(--font-serif)]">Editar Libro</h2>
            <form id="editForm" method="POST" class="space-y-3">
                @csrf @method('PATCH')
                <x-field label="Título"><input type="text" name="title" id="editTitle" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <x-field label="Autor"><input type="text" name="author" id="editAuthor" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <x-field label="ISBN"><input type="text" name="isbn" id="editIsbn" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <x-field label="Categoría"><input type="text" name="category" id="editCategory" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <div class="flex justify-end gap-2 pt-2">
                    <x-button type="button" variant="ghost" size="sm" onclick="closeModals()">Cancelar</x-button>
                    <x-button type="submit" variant="primary" size="sm">Guardar</x-button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalStock" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true">
        <div class="w-full max-w-sm rounded-[var(--radius-xl)] border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)]">
            <h2 class="mb-2 text-lg font-semibold font-[family-name:var(--font-serif)]">Actualizar Stock</h2>
            <p id="stockBookTitle" class="text-sm text-[var(--color-muted-foreground)] mb-4"></p>
            <form id="stockForm" method="POST" class="space-y-3">
                @csrf @method('PATCH')
                <x-field label="Stock total"><input type="number" name="stock_total" min="1" required class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)]"></x-field>
                <div class="flex justify-end gap-2 pt-2">
                    <x-button type="button" variant="ghost" size="sm" onclick="closeModals()">Cancelar</x-button>
                    <x-button type="submit" variant="primary" size="sm">Actualizar</x-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal(){ document.getElementById('modalCreate').classList.remove('hidden'); }
        function closeModals(){ ['modalCreate','modalEdit','modalStock'].forEach(id=>document.getElementById(id).classList.add('hidden')); }
        function openEditModal(id,title,author,isbn,category){
            document.getElementById('editForm').action = '/library/books/'+id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editAuthor').value = author;
            document.getElementById('editIsbn').value = isbn;
            document.getElementById('editCategory').value = category;
            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function openStockModal(id,title){
            document.getElementById('stockForm').action = '/library/books/'+id+'/stock';
            document.getElementById('stockBookTitle').textContent = title;
            document.getElementById('modalStock').classList.remove('hidden');
        }
        document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeModals(); });
    </script>
</x-page>
@endsection
