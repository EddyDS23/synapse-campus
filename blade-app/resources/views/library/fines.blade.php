@extends('layouts.app')

@section('title', 'Mis Multas | Synapse')

@section('content')
@php
    $pendingFines = collect($fines)->filter(fn ($fine) => ($fine['status'] ?? null) === 'pending');
    $paidFines = collect($fines)->filter(fn ($fine) => ($fine['status'] ?? null) === 'paid');
    $totalPending = $pendingFines->sum('amount');
    $totalPaid = $paidFines->sum('amount');
    $totalPages = (int) ($meta['totalPage'] ?? 1);
    $isPaginated = $totalPages > 1;
@endphp

<x-page>
    <header class="mb-8">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--color-muted-foreground)]">Biblioteca</p>
        <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
            Mis multas
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
            Revisa los cargos generados por devoluciones tardías y liquida los que estén pendientes.
        </p>
    </header>

    @if ($errors->any())
        <x-alert type="error" class="mb-6">{{ $errors->first('error') }}</x-alert>
    @endif

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    <section aria-label="Resumen de multas" class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat
            label="Deuda pendiente"
            :value="'$' . number_format($totalPending, 2)"
            :tone="$totalPending > 0 ? 'danger' : 'success'"
            :meta="$isPaginated ? 'Registros de esta página.' : null"
        />
        <x-stat
            label="Multas registradas"
            :value="$meta['total'] ?? count($fines)"
            tone="primary"
        />
        <x-stat
            label="Monto pagado"
            :value="'$' . number_format($totalPaid, 2)"
            tone="success"
            :meta="$isPaginated ? 'Registros de esta página.' : null"
        />
    </section>

    <x-card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-[var(--color-border)] p-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold text-[var(--color-foreground)]">Historial de cargos</h2>

            <form method="GET" action="{{ route('library.fines') }}" class="flex items-center gap-2">
                <label for="status" class="sr-only">Estado</label>
                <select
                    id="status"
                    name="status"
                    class="h-9 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-sm text-[var(--color-foreground)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)]"
                >
                    <option value="">Todos los estados</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pendientes</option>
                    <option value="paid" @selected(request('status') === 'paid')>Pagadas</option>
                </select>
                <x-button type="submit" variant="ghost" size="sm">
                    <x-icon name="filter" class="h-3.5 w-3.5" />
                    Filtrar
                </x-button>
                @if (request()->filled('status'))
                    <a href="{{ route('library.fines') }}" class="text-xs font-medium text-[var(--color-muted-foreground)] underline-offset-2 hover:underline">
                        Limpiar
                    </a>
                @endif
            </form>
        </div>

        @if (empty($fines))
            <div class="p-6">
                <x-empty-state
                    title="Sin multas en esta categoría"
                    description="No hay cargos registrados con el filtro seleccionado."
                />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-[var(--color-border)] text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--color-muted-foreground)]">
                            <th scope="col" class="px-5 py-3">Monto</th>
                            <th scope="col" class="px-5 py-3">Estado</th>
                            <th scope="col" class="px-5 py-3">Cronología</th>
                            <th scope="col" class="px-5 py-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-border)]">
                        @foreach ($fines as $fine)
                            @php $isPending = ($fine['status'] ?? null) === 'pending'; @endphp
                            <tr class="transition-colors hover:bg-[var(--color-muted)]/40">
                                <td class="px-5 py-4 font-mono text-base font-semibold text-[var(--color-foreground)]">
                                    ${{ number_format($fine['amount'], 2) }}
                                </td>
                                <td class="px-5 py-4">
                                    <x-badge :variant="$isPending ? 'danger' : 'success'">
                                        {{ $isPending ? 'Pendiente' : 'Pagada' }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-4 text-xs text-[var(--color-muted-foreground)]">
                                    <div>
                                        <span class="font-semibold uppercase tracking-wide">Generada:</span>
                                        {{ $fine['created_at'] ? \Carbon\Carbon::parse($fine['created_at'])->format('d/m/Y') : '—' }}
                                    </div>
                                    @if ($fine['paid_at'])
                                        <div class="mt-0.5 text-[var(--color-success)]">
                                            <span class="font-semibold uppercase tracking-wide">Pagada:</span>
                                            {{ \Carbon\Carbon::parse($fine['paid_at'])->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if ($isPending)
                                        <form method="POST" action="{{ route('library.fine.pay', $fine['id']) }}">
                                            @csrf
                                            <x-button type="submit" variant="primary" size="sm">
                                                <x-icon name="credit-card" class="h-4 w-4" />
                                                Pagar
                                            </x-button>
                                        </form>
                                    @else
                                        <span class="text-xs text-[var(--color-muted-foreground)]">Sin acciones</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-[var(--color-border)] px-5 py-3 text-xs text-[var(--color-muted-foreground)]">
                <span>Página {{ $meta['currentPage'] ?? 1 }} de {{ $totalPages }}</span>
                <span>{{ $meta['total'] ?? count($fines) }} registros</span>
            </div>
        @endif
    </x-card>
</x-page>
@endsection
