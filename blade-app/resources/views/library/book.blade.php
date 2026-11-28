@extends('layouts.app')

@section('title', ($book['title'] ?? 'Detalle de libro') . ' | Synapse Campus')

@section('content')
<x-page class="max-w-4xl">
    <x-breadcrumb :items="[
        ['label' => 'Biblioteca', 'url' => route('library.books')],
        ['label' => 'Catálogo', 'url' => route('library.books')],
        ['label' => $book['title'] ?? 'Libro'],
    ]" />

    @if ($book)
    @php
    $available = (int) ($book['stock_available'] ?? 0);
    $total = (int) ($book['stock_total'] ?? $book['total_stock'] ?? 0);
    $hasStock = $available > 0;
    @endphp

    <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] overflow-hidden">
        {{-- 30% Secundario: cabecera oscura con acento carmesí --}}
        <div class="bg-[var(--color-chrome-bg)] px-6 sm:px-8 py-6 border-b border-[var(--color-chrome-border)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-rose-200">
                        {{ $book['category'] ?? 'Biblioteca' }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight leading-snug">
                    {{ $book['title'] ?? 'Sin título' }}
                </h1>
                @if (!empty($book['author']))
                <p class="text-sm sm:text-base text-stone-300 mt-1">
                    {{ $book['author'] }}
                </p>
                @endif
            </div>

          
        </div>

        {{-- 60% Neutro: ficha técnica y acción principal (10% carmesí) --}}
        <div class="p-6 sm:p-8 space-y-6">
            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-[var(--color-muted)]/60 border border-[var(--color-border)]">
                    <dt class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">ISBN</dt>
                    <dd class="font-mono text-sm sm:text-base font-bold text-[var(--color-foreground)] mt-1.5">
                        {{ $book['isbn'] ?? '—' }}
                    </dd>
                </div>

                <div class="p-4 rounded-xl bg-[var(--color-muted)]/60 border border-[var(--color-border)]">
                    <dt class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Categoría</dt>
                    <dd class="text-sm sm:text-base font-semibold text-[var(--color-foreground)] mt-1.5">
                        {{ $book['category'] ?? '—' }}
                    </dd>
                </div>

                <div class="p-4 rounded-xl bg-[var(--color-muted)]/60 border border-[var(--color-border)]">
                    <dt class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Stock disponible</dt>
                    <dd class="font-mono text-sm sm:text-base font-bold text-[var(--color-foreground)] mt-1.5">
                        @if ($total > 0)
                        {{ $available }} <span class="text-[var(--color-muted-foreground)] font-normal">/</span> {{ $total }}
                        @else
                        {{ $available }} {{ $available === 1 ? 'ejemplar' : 'ejemplares' }}
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="pt-5 border-t border-[var(--color-border)] flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                <a
                    href="{{ route('library.books') }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-4 text-sm font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors">
                    <x-icon name="arrow-left" class="w-4 h-4" />
                    Volver al catálogo
                </a>

                @if ($hasStock)
                <form action="{{ route('library.loan.request', $book['id']) }}" method="POST">
                    @csrf
                    <x-button type="submit" variant="primary" size="md">
                        <x-icon name="book-open" class="w-4 h-4" />
                        Solicitar préstamo
                    </x-button>
                </form>
                @else
                <x-button variant="ghost" size="md" disabled>
                    Sin stock disponible
                </x-button>
                @endif
            </div>
        </div>
    </article>
    @else
    <x-empty-state title="Libro no encontrado" description="El libro solicitado no existe o no está disponible." />
    @endif
</x-page>
@endsection