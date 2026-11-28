@extends('layouts.app')

@section('title', 'Catálogo de Libros | Synapse Campus')

@section('content')
@php
    $currentPage = (int) ($meta['currentPage'] ?? 1);
    $totalPages  = (int) ($meta['totalPage'] ?? 1);
@endphp

<x-page>
    <header class="bg-[var(--color-card)] p-6 sm:p-7 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)]">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Biblioteca</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
            Catálogo de Libros
        </h1>
        <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1">
            Explora la base de conocimientos Synapse y solicita ejemplares disponibles.
        </p>
    </header>

    {{-- Filtros --}}
    <section aria-label="Filtros del catálogo">
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)]">
            <form method="GET" action="{{ route('library.books') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[180px]">
                    <label for="filter-title" class="block text-xs font-bold text-[var(--color-muted-foreground)] uppercase tracking-wider mb-1.5">Título</label>
                    <input
                        id="filter-title"
                        type="text"
                        name="title"
                        placeholder="Buscar título..."
                        value="{{ request('title') }}"
                        class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </div>

                <div class="flex-1 min-w-[180px]">
                    <label for="filter-autor" class="block text-xs font-bold text-[var(--color-muted-foreground)] uppercase tracking-wider mb-1.5">Autor</label>
                    <input
                        id="filter-autor"
                        type="text"
                        name="autor"
                        placeholder="Nombre del autor..."
                        value="{{ request('autor') }}"
                        class="w-full h-10 px-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </div>

                <div class="w-full md:w-44">
                    <label for="filter-category" class="block text-xs font-bold text-[var(--color-muted-foreground)] uppercase tracking-wider mb-1.5">Categoría</label>
                    <input
                        id="filter-category"
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
                        id="available"
                        {{ request('available') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-[var(--color-border)] text-[var(--color-primary)] focus:ring-[var(--color-primary)]"
                    >
                    <label for="available" class="text-sm font-medium text-[var(--color-foreground)] cursor-pointer">Solo disponibles</label>
                </div>

                <div class="flex gap-2">
                    <x-button type="submit" variant="primary" size="sm">
                        <x-icon name="search" class="w-3.5 h-3.5" />
                        Buscar
                    </x-button>
                    <a
                        href="{{ route('library.books') }}"
                        class="inline-flex h-8 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-xs font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </section>

    @if (empty($books))
        <x-empty-state title="Sin coincidencias" description="Ajusta los filtros para encontrar lo que buscas." />
    @else
        {{-- Grid compacto de libros --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 items-stretch">
            @foreach ($books as $book)
                @php $available = ($book['stock_available'] ?? 0) > 0; @endphp
                <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-4 shadow-[var(--shadow-sm)] hover:border-[var(--color-primary)]/40 transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-[var(--color-primary)] bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20 px-2.5 py-0.5 rounded-md truncate max-w-[65%]">
                                <x-icon name="book" class="w-3.5 h-3.5 shrink-0" />
                                <span class="truncate">{{ $book['category'] ?? 'General' }}</span>
                            </span>
                            <x-badge :variant="$available ? 'success' : 'danger'">
                                {{ $available ? ($book['stock_available'] . ' disp.') : 'Agotado' }}
                            </x-badge>
                        </div>

                        <h2 class="text-base font-bold text-[var(--color-foreground)] leading-snug line-clamp-2" title="{{ $book['title'] }}">
                            {{ $book['title'] }}
                        </h2>
                        <p class="text-sm text-[var(--color-muted-foreground)] mt-1 truncate">
                            {{ $book['author'] }}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-[var(--color-border)] flex items-center justify-between gap-2">
                        <a
                            href="{{ route('library.book', $book['id']) }}"
                            class="text-xs font-semibold text-[var(--color-muted-foreground)] hover:text-[var(--color-foreground)] transition-colors"
                        >
                            Detalles
                        </a>

                        @if ($available)
                            <form method="POST" action="{{ route('library.loan.request', $book['id']) }}">
                                @csrf
                                <x-button type="submit" variant="primary" size="sm">
                                    Solicitar
                                </x-button>
                            </form>
                        @else
                            <x-button variant="ghost" size="sm" disabled>
                                Agotado
                            </x-button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Barra de paginación compacta centrada --}}
        <div class="flex justify-center pt-1">
            <nav aria-label="Paginación del catálogo" class="inline-flex flex-wrap items-center gap-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] px-4 py-2.5 shadow-[var(--shadow-sm)] text-xs text-[var(--color-muted-foreground)]">
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
@endsection