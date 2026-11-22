@extends('layouts.app')

@section('title', 'Detalle de libro - Synapse Campus')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-breadcrumb :items="[
        ['label' => 'Biblioteca', 'url' => route('library.books')],
        ['label' => 'Catálogo', 'url' => route('library.books')],
        ['label' => $book['title'] ?? 'Libro'],
    ]" />

    @if ($book)
        <x-card :title="$book['title'] ?? 'Sin título'" :subtitle="$book['author'] ?? ''">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-[var(--color-muted-foreground)]">ISBN</p>
                        <p class="font-mono text-sm">{{ $book['isbn'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-[var(--color-muted-foreground)]">Categoría</p>
                        <p class="text-sm">{{ $book['category'] ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-[var(--color-muted-foreground)]">Stock disponible</p>
                        <p class="text-sm font-semibold">{{ $book['stock_available'] ?? 0 }} / {{ $book['stock_total'] ?? 0 }}</p>
                    </div>
                </div>
                <div class="flex items-end justify-end">
                    <form action="{{ route('library.loan.request', $book['id']) }}" method="POST">
                        @csrf
                        <x-button type="submit" variant="primary">Solicitar préstamo</x-button>
                    </form>
                </div>
            </div>
        </x-card>
    @else
        <x-empty-state title="Libro no encontrado" description="El libro solicitado no existe o no está disponible." />
    @endif
</div>
@endsection
