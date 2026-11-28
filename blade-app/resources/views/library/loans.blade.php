@extends('layouts.app')

@section('title', 'Mis Préstamos | Synapse Campus')

@section('content')
@php
    $activeLoans = collect($loans)->filter(fn ($loan) => ($loan['status'] ?? null) === 'active');
    $overdueLoans = $activeLoans->filter(fn ($loan) => \Carbon\Carbon::parse($loan['due_at'])->isPast());
    $nextDue = $activeLoans
        ->map(fn ($loan) => \Carbon\Carbon::parse($loan['due_at']))
        ->filter(fn ($due) => $due->isFuture())
        ->sort()
        ->first();
@endphp

<x-page>
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[var(--color-card)] p-6 sm:p-7 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)]">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
                <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Biblioteca</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
                Mis préstamos
            </h1>
            <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1">
                Consulta los ejemplares que tienes en préstamo, sus fechas de devolución y tu saldo de renovaciones.
            </p>
        </div>

        <x-button :href="route('library.books')" variant="primary" size="sm" class="self-start sm:self-center shrink-0">
            <x-icon name="book-open" class="w-4 h-4" />
            Explorar catálogo
        </x-button>
    </header>

    @if ($activeLoans->isEmpty() && empty($loans))
        <x-card>
            <x-empty-state
                title="No tienes préstamos activos"
                description="Cuando solicites un ejemplar del catálogo aparecerá aquí con su fecha de devolución."
            >
                <x-slot:action>
                    <x-button :href="route('library.books')" variant="primary" size="sm">
                        <x-icon name="book-open" class="w-4 h-4" />
                        Explorar catálogo
                    </x-button>
                </x-slot:action>
            </x-empty-state>
        </x-card>
    @else
        {{-- Resumen real de la cuenta --}}
        <section aria-label="Resumen de préstamos" class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <x-stat label="Préstamos activos" :value="$activeLoans->count()" tone="primary" />
            <x-stat
                label="Fuera de plazo"
                :value="$overdueLoans->count()"
                :tone="$overdueLoans->isNotEmpty() ? 'danger' : 'success'"
                :meta="$overdueLoans->isNotEmpty() ? 'Regulariza la devolución para evitar multas.' : 'Todos tus préstamos están en tiempo.'"
            />
            <x-stat
                label="Próxima devolución"
                :value="$nextDue ? $nextDue->translatedFormat('d M') : '—'"
                tone="neutral"
                :meta="$nextDue ? $nextDue->translatedFormat('l, d \d\e F') : 'Sin devoluciones pendientes.'"
            />
        </section>

        <section aria-labelledby="loans-heading" class="space-y-3.5">
            <h2 id="loans-heading" class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                Ejemplares en tu cuenta
            </h2>

            <div class="grid grid-cols-1 gap-4">
                @foreach ($loans as $loan)
                    @php
                        $borrowedAt = \Carbon\Carbon::parse($loan['borrowed_at']);
                        $dueAt = \Carbon\Carbon::parse($loan['due_at']);
                        $daysRemaining = (int) floor(now()->startOfDay()->diffInDays($dueAt->copy()->startOfDay(), false));
                        $isOverdue = $daysRemaining < 0;
                        $totalDays = max(1, (int) floor($borrowedAt->startOfDay()->diffInDays($dueAt->copy()->startOfDay())));
                        $elapsedDays = (int) floor($borrowedAt->startOfDay()->diffInDays(now()->startOfDay()));
                        $percentage = (int) max(0, min(100, ($elapsedDays / $totalDays) * 100));
                        $isActive = ($loan['status'] ?? null) === 'active';
                        $renewalsLeft = max(0, 3 - (int) ($loan['renew_count'] ?? 0));
                    @endphp

                    <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-5 sm:p-6 shadow-[var(--shadow-sm)] transition-all hover:border-[var(--color-primary)]/40">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
                            <div class="flex min-w-0 flex-1 items-start gap-4">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[var(--color-primary)]/20 bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                                    <x-icon name="book" class="h-6 w-6" />
                                </span>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-mono text-sm sm:text-base font-bold text-[var(--color-foreground)]">
                                            Ejemplar #{{ $loan['id'] }}
                                        </h3>
                                        @if ($isActive)
                                            <x-badge :variant="$isOverdue ? 'danger' : 'success'">
                                                {{ $isOverdue ? 'Vencido' : 'En tiempo' }}
                                            </x-badge>
                                        @else
                                            <x-badge variant="neutral">{{ ucfirst(str_replace('_', ' ', $loan['status'] ?? '—')) }}</x-badge>
                                        @endif
                                    </div>

                                    <p class="mt-1 font-mono text-xs text-[var(--color-muted-foreground)]">
                                        Libro: {{ $loan['book_id'] }}
                                    </p>

                                    <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-xs sm:text-sm">
                                        <div>
                                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Prestado</dt>
                                            <dd class="mt-0.5 font-medium text-[var(--color-foreground)]">{{ $borrowedAt->translatedFormat('d M Y') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Devolución</dt>
                                            <dd class="mt-0.5 {{ $isOverdue ? 'font-bold text-[var(--color-destructive)]' : 'font-medium text-[var(--color-foreground)]' }}">
                                                {{ $dueAt->translatedFormat('d M Y') }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Renovaciones</dt>
                                            <dd class="mt-0.5 font-medium text-[var(--color-foreground)]">
                                                {{ $loan['renew_count'] ?? 0 }} / 3
                                                @if ($isActive)
                                                    <span class="text-xs text-[var(--color-muted-foreground)]">· {{ $renewalsLeft }} disponibles</span>
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center lg:shrink-0">
                                <div class="w-full sm:w-44">
                                    <div class="mb-1.5 flex items-center justify-between text-[11px] font-bold uppercase tracking-wider">
                                        <span class="{{ $isOverdue ? 'text-[var(--color-destructive)]' : 'text-[var(--color-muted-foreground)]' }}">
                                            @if ($isOverdue)
                                                {{ abs($daysRemaining) }} {{ abs($daysRemaining) === 1 ? 'día de retraso' : 'días de retraso' }}
                                            @else
                                                {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'día restante' : 'días restantes' }}
                                            @endif
                                        </span>
                                        <span class="text-[var(--color-muted-foreground)]">{{ $percentage }}%</span>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-[var(--color-muted)]">
                                        <div class="h-full rounded-full {{ $isOverdue ? 'bg-[var(--color-destructive)]' : 'bg-[var(--color-primary)]' }}" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>

                                @if ($isActive)
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('library.loan.renew', $loan['id']) }}">
                                            @csrf
                                            <x-button type="submit" variant="ghost" size="sm" :disabled="$renewalsLeft === 0">
                                                <x-icon name="rotate-cw" class="h-3.5 w-3.5" />
                                                Renovar
                                            </x-button>
                                        </form>
                                        <form method="POST" action="{{ route('library.loan.return', $loan['id']) }}">
                                            @csrf
                                            <x-button type="submit" variant="primary" size="sm">
                                                <x-icon name="corner-down-left" class="h-3.5 w-3.5" />
                                                Devolver
                                            </x-button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</x-page>
@endsection
