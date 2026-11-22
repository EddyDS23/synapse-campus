@extends('layouts.app')

@section('title', 'Catálogo de Libros | Synapse')

@section('content')
<x-page>
    <header class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">Catálogo de <span class="text-[var(--color-primary)]">Libros</span></h1>
            <p class="text-[var(--color-muted-foreground)] mt-1">Explora la base de conocimientos Synapse.</p>
        </div>
    </header>

    <!-- Filtros -->
    <section class="mb-6">
        <x-card padding="p-4">
            <form method="GET" action="{{ route('library.books') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-bold text-[var(--color-muted-foreground)] uppercase mb-1">Título</label>
                    <input type="text" name="title" placeholder="Buscar título..." value="{{ request('title') }}" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-bold text-[var(--color-muted-foreground)] uppercase mb-1">Autor</label>
                    <input type="text" name="autor" placeholder="Nombre del autor..." value="{{ request('autor') }}" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </div>
                <div class="w-full md:w-48">
                    <label class="block text-[10px] font-bold text-[var(--color-muted-foreground)] uppercase mb-1">Categoría</label>
                    <input type="text" name="category" placeholder="Filtrar tema" value="{{ request('category') }}" class="w-full h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none">
                </div>
                <div class="flex items-center gap-2 mb-1">
                    <input type="checkbox" name="available" value="1" id="available" {{ request('available') ? 'checked' : '' }} class="w-4 h-4 rounded border-[var(--color-border)] text-[var(--color-primary)] focus:ring-[var(--color-primary)]">
                    <label for="available" class="text-sm text-[var(--color-muted-foreground)] cursor-pointer">Solo disponibles</label>
                </div>
                <div class="flex gap-2">
                    <x-button type="submit" variant="primary" size="sm">Buscar</x-button>
                    <a href="{{ route('library.books') }}" class="inline-flex items-center justify-center h-9 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] text-sm hover:bg-[var(--color-muted)]">Reset</a>
                </div>
            </form>
        </x-card>
    </section>

    @if(empty($books))
        <x-empty-state title="Sin coincidencias" description="Ajusta los filtros para encontrar lo que buscas." />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach($books as $book)
                <x-card padding="p-5" hover>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-12 rounded-[var(--radius-sm)] bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20 flex items-center justify-center text-[var(--color-primary)] font-bold text-[10px]">
                            LIB
                        </div>
                        @if(($book['stock_available'] ?? 0) > 0)
                            <x-badge variant="success">{{ $book['stock_available'] }} Disp.</x-badge>
                        @else
                            <x-badge variant="danger">Agotado</x-badge>
                        @endif
                    </div>

                    <div class="mb-4 flex-grow">
                        <p class="text-[10px] font-bold text-[var(--color-primary)] uppercase tracking-widest mb-1">{{ $book['category'] ?? '' }}</p>
                        <h4 class="text-base font-bold text-[var(--color-foreground)] leading-tight mb-1">{{ $book['title'] }}</h4>
                        <p class="text-sm text-[var(--color-muted-foreground)]">{{ $book['author'] }}</p>
                    </div>

                    <div class="pt-3 border-t border-[var(--color-border)]">
                        @if(($book['stock_available'] ?? 0) > 0)
                            <form method="POST" action="{{ route('library.loan.request', $book['id']) }}">
                                @csrf
                                <x-button type="submit" variant="primary" size="sm" class="w-full">Solicitar Préstamo</x-button>
                            </form>
                        @else
                            <x-button variant="ghost" size="sm" class="w-full" disabled>No Disponible</x-button>
                        @endif
                    </div>
                </x-card>
            @endforeach
        </div>

        <!-- Paginación -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 border-t border-[var(--color-border)] pt-4">
            <div class="text-sm text-[var(--color-muted-foreground)]">
                Página <span class="font-bold text-[var(--color-foreground)]">{{ $meta['currentPage'] ?? 1 }}</span> de <span class="font-bold text-[var(--color-foreground)]">{{ $meta['totalPage'] ?? 1 }}</span>
                <span class="mx-2 text-[var(--color-border)]">|</span>
                Total: <span class="font-bold text-[var(--color-primary)]">{{ $meta['total'] ?? 0 }}</span> resultados
            </div>
        </div>
    @endif
</x-page>
@endsection
