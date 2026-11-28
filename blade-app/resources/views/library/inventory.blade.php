@extends('layouts.app')

@section('title', 'Inventario de Libros | Synapse Campus')

@section('content')
@php
    $currentPage = (int) ($meta['currentPage'] ?? 1);
    $totalPages  = (int) ($meta['totalPage'] ?? 1);
@endphp

<x-page>
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[var(--color-card)] p-6 sm:p-7 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)]">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
                <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Biblioteca</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
                Inventario de libros
            </h1>
            <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1">
                Gestión del catálogo bibliográfico, metadatos y existencias disponibles.
            </p>
        </div>

        <x-button type="button" onclick="openCreateModal()" variant="primary" size="sm" class="self-start sm:self-center shrink-0">
            <x-icon name="plus" class="w-4 h-4" />
            Agregar libro
        </x-button>
    </header>

    {{-- Filtros --}}
    <section aria-label="Filtros de inventario">
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)]">
            <form method="GET" action="{{ route('library.inventory') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[180px]">
                    <label for="inv-title" class="block text-xs font-bold text-[var(--color-muted-foreground)] uppercase tracking-wider mb-1.5">Título</label>
                    <input
                        id="inv-title"
                        type="text"
                        name="title"
                        placeholder="Buscar título..."
                        value="{{ request('title') }}"
                        class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </div>

                <div class="flex-1 min-w-[180px]">
                    <label for="inv-autor" class="block text-xs font-bold text-[var(--color-muted-foreground)] uppercase tracking-wider mb-1.5">Autor</label>
                    <input
                        id="inv-autor"
                        type="text"
                        name="autor"
                        placeholder="Nombre del autor..."
                        value="{{ request('autor') }}"
                        class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </div>

                <div class="w-full md:w-44">
                    <label for="inv-category" class="block text-xs font-bold text-[var(--color-muted-foreground)] uppercase tracking-wider mb-1.5">Categoría</label>
                    <input
                        id="inv-category"
                        type="text"
                        name="category"
                        placeholder="Filtrar tema..."
                        value="{{ request('category') }}"
                        class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </div>

                <div class="flex items-center gap-2 h-10">
                    <input
                        type="checkbox"
                        name="available"
                        value="1"
                        id="inv-available"
                        {{ request('available') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-[var(--color-border)] text-[var(--color-primary)] focus:ring-[var(--color-primary)]"
                    >
                    <label for="inv-available" class="text-sm font-medium text-[var(--color-foreground)] cursor-pointer">Solo disponibles</label>
                </div>

                <div class="flex gap-2">
                    <x-button type="submit" variant="primary" size="sm">
                        <x-icon name="filter" class="w-3.5 h-3.5" />
                        Filtrar
                    </x-button>
                    <a
                        href="{{ route('library.inventory') }}"
                        class="inline-flex h-8 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-xs font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors"
                    >
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </section>

    {{-- Tabla de inventario --}}
    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[var(--color-muted)]/60 border-b border-[var(--color-border)] text-xs font-bold text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <th scope="col" class="px-6 py-3.5">Título</th>
                        <th scope="col" class="px-6 py-3.5">Autor</th>
                        <th scope="col" class="px-6 py-3.5">Categoría</th>
                        <th scope="col" class="px-6 py-3.5 text-center">Stock</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)] text-xs sm:text-sm">
                    @forelse ($books as $book)
                        @php
                            $available = (int) ($book['stock_available'] ?? 0);
                            $total     = (int) ($book['stock_total'] ?? 0);
                        @endphp
                        <tr class="hover:bg-[var(--color-muted)]/35 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-[var(--color-foreground)]">{{ $book['title'] }}</div>
                                @if (!empty($book['isbn']))
                                    <div class="font-mono text-[11px] text-[var(--color-muted-foreground)] mt-0.5">ISBN: {{ $book['isbn'] }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-[var(--color-muted-foreground)]">{{ $book['author'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold uppercase tracking-wider bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20 text-[var(--color-primary)]">
                                    {{ $book['category'] ?? 'General' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-mono whitespace-nowrap">
                                <span class="text-sm font-bold {{ $available > 0 ? 'text-[var(--color-foreground)]' : 'text-[var(--color-destructive)]' }}">
                                    {{ $available }}
                                </span>
                                <span class="text-xs text-[var(--color-muted-foreground)]">/ {{ $total }}</span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <button
                                        type="button"
                                        data-id="{{ $book['id'] }}"
                                        data-title="{{ $book['title'] }}"
                                        data-author="{{ $book['author'] }}"
                                        data-isbn="{{ $book['isbn'] ?? '' }}"
                                        data-category="{{ $book['category'] ?? '' }}"
                                        onclick="openEditModal(this)"
                                        class="inline-flex h-8 items-center gap-1 rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-xs font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors cursor-pointer"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        type="button"
                                        data-id="{{ $book['id'] }}"
                                        data-title="{{ $book['title'] }}"
                                        data-stock="{{ $total }}"
                                        onclick="openStockModal(this)"
                                        class="inline-flex h-8 items-center gap-1 rounded-xl border border-[var(--color-primary)]/25 bg-[var(--color-primary)]/10 px-3 text-xs font-semibold text-[var(--color-primary)] hover:bg-[var(--color-primary)]/15 transition-colors cursor-pointer"
                                    >
                                        Stock
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-[var(--color-muted-foreground)]">
                                No hay libros registrados en el inventario.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación compacta centrada --}}
    @if (!empty($books) && $totalPages > 1)
        <div class="flex justify-center pt-1">
            <nav aria-label="Paginación del inventario" class="inline-flex flex-wrap items-center gap-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] px-4 py-2.5 shadow-[var(--shadow-sm)] text-xs text-[var(--color-muted-foreground)]">
                @if ($currentPage > 1)
                    <a
                        href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}"
                        class="inline-flex items-center gap-1 rounded-lg border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-2.5 py-1 font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors"
                    >
                        <x-icon name="chevron-left" class="w-3.5 h-3.5" />
                        Anterior
                    </a>
                @endif

                <span>
                    Página <strong class="text-[var(--color-foreground)]">{{ $currentPage }}</strong> de <strong class="text-[var(--color-foreground)]">{{ $totalPages }}</strong>
                    <span class="mx-2 text-[var(--color-border)]">•</span>
                    <strong class="text-[var(--color-primary)]">{{ $meta['total'] ?? count($books) }}</strong> resultados
                </span>

                @if ($currentPage < $totalPages)
                    <a
                        href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}"
                        class="inline-flex items-center gap-1 rounded-lg border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-2.5 py-1 font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors"
                    >
                        Siguiente
                        <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                    </a>
                @endif
            </nav>
        </div>
    @endif
</x-page>

{{-- Modal: Agregar Libro --}}
<div id="modalCreate" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modalCreateTitle">
    <div class="w-full max-w-lg rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)] my-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 id="modalCreateTitle" class="text-base sm:text-lg font-bold text-[var(--color-foreground)]">Agregar libro al acervo</h2>
            <button type="button" onclick="closeModals()" class="p-1.5 rounded-xl text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors cursor-pointer" aria-label="Cerrar">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>

        <form method="POST" action="{{ route('library.inventory.create') }}" class="space-y-3.5">
            @csrf
            <x-field label="Título">
                <input type="text" name="title" required class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
            </x-field>
            <x-field label="Autor">
                <input type="text" name="author" required class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
            </x-field>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <x-field label="ISBN">
                    <input type="text" name="isbn" required class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 font-mono text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </x-field>
                <x-field label="Categoría">
                    <input type="text" name="category" required class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </x-field>
            </div>
            <x-field label="Stock total">
                <input type="number" name="stock_total" min="1" required class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 font-mono text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
            </x-field>

            <div class="flex justify-end gap-2 pt-3 border-t border-[var(--color-border)]">
                <x-button type="button" variant="ghost" size="sm" onclick="closeModals()">Cancelar</x-button>
                <x-button type="submit" variant="primary" size="sm">Guardar libro</x-button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Editar Libro --}}
<div id="modalEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
    <div class="w-full max-w-lg rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)] my-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 id="modalEditTitle" class="text-base sm:text-lg font-bold text-[var(--color-foreground)]">Editar libro</h2>
            <button type="button" onclick="closeModals()" class="p-1.5 rounded-xl text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors cursor-pointer" aria-label="Cerrar">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>

        <form id="editForm" method="POST" class="space-y-3.5">
            @csrf
            @method('PATCH')
            <x-field label="Título">
                <input type="text" name="title" id="editTitle" required class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
            </x-field>
            <x-field label="Autor">
                <input type="text" name="author" id="editAuthor" required class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
            </x-field>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <x-field label="ISBN">
                    <input type="text" name="isbn" id="editIsbn" class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 font-mono text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </x-field>
                <x-field label="Categoría">
                    <input type="text" name="category" id="editCategory" required class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </x-field>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-[var(--color-border)]">
                <x-button type="button" variant="ghost" size="sm" onclick="closeModals()">Cancelar</x-button>
                <x-button type="submit" variant="primary" size="sm">Guardar cambios</x-button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Actualizar Stock --}}
<div id="modalStock" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modalStockTitle">
    <div class="w-full max-w-sm rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)] my-auto">
        <div class="flex items-center justify-between mb-1">
            <h2 id="modalStockTitle" class="text-base sm:text-lg font-bold text-[var(--color-foreground)]">Actualizar stock</h2>
            <button type="button" onclick="closeModals()" class="p-1.5 rounded-xl text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors cursor-pointer" aria-label="Cerrar">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>
        <p id="stockBookTitle" class="text-xs sm:text-sm text-[var(--color-muted-foreground)] mb-4"></p>

        <form id="stockForm" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <x-field label="Stock total">
                <input type="number" name="stock_total" id="stockTotalInput" min="1" required class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 font-mono text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
            </x-field>

            <div class="flex justify-end gap-2 pt-2 border-t border-[var(--color-border)]">
                <x-button type="button" variant="ghost" size="sm" onclick="closeModals()">Cancelar</x-button>
                <x-button type="submit" variant="primary" size="sm">Actualizar</x-button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalCreate').classList.remove('hidden');
    }

    function closeModals() {
        ['modalCreate', 'modalEdit', 'modalStock'].forEach(function (id) {
            document.getElementById(id).classList.add('hidden');
        });
    }

    function openEditModal(button) {
        const { id, title, author, isbn, category } = button.dataset;
        document.getElementById('editForm').action = '/library/books/' + id;
        document.getElementById('editTitle').value = title || '';
        document.getElementById('editAuthor').value = author || '';
        document.getElementById('editIsbn').value = isbn || '';
        document.getElementById('editCategory').value = category || '';
        document.getElementById('modalEdit').classList.remove('hidden');
    }

    function openStockModal(button) {
        const { id, title, stock } = button.dataset;
        document.getElementById('stockForm').action = '/library/books/' + id + '/stock';
        document.getElementById('stockBookTitle').textContent = title || '';
        document.getElementById('stockTotalInput').value = stock || 1;
        document.getElementById('modalStock').classList.remove('hidden');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModals();
    });

    ['modalCreate', 'modalEdit', 'modalStock'].forEach(function (id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModals();
            });
        }
    });
</script>
@endsection