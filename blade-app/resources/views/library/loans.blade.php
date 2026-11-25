@extends('layouts.app')

@section('title', 'Mis Préstamos | Synapse')

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
    <header class="mb-8">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--color-muted-foreground)]">Biblioteca</p>
        <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
            Mis préstamos
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
            Consulta los ejemplares que tienes en préstamo, sus fechas de devolución y tu saldo de renovaciones.
        </p>
    </header>

    @if ($errors->any())
        <x-alert type="error" class="mb-6">{{ $errors->first('error') }}</x-alert>
    @endif

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    @if ($activeLoans->isEmpty())
        <x-card>
            <x-empty-state
                title="No tienes préstamos activos"
                description="Cuando solicites un ejemplar del catálogo aparecerá aquí con su fecha de devolución."
            >
                <x-slot:action>
                    <x-button :href="route('library.books')" variant="primary" size="sm">
                        <x-icon name="book-open" class="h-4 w-4" />
                        Explorar catálogo
                    </x-button>
                </x-slot:action>
            </x-empty-state>
        </x-card>
    @else
        {{-- Resumen real de la cuenta --}}
        <section aria-label="Resumen de préstamos" class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
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
                tone="accent"
                :meta="$nextDue ? $nextDue->translatedFormat('l, d \d\e F') : 'Sin devoluciones pendientes.'"
            />
        </section>

        <section aria-labelledby="loans-heading">
            <h2 id="loans-heading" class="mb-3 text-sm font-semibold uppercase tracking-[0.14em] text-[var(--color-muted-foreground)]">
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

                    <article class="rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)]">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
                            <div class="flex min-w-0 flex-1 items-start gap-4">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[var(--radius-md)] border border-[var(--color-primary)]/15 bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                                    <x-icon name="book" class="h-6 w-6" />
                                </span>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-mono text-sm font-semibold text-[var(--color-foreground)]">
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

                                    <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-xs">
                                        <div>
                                            <dt class="font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Prestado</dt>
                                            <dd class="mt-0.5 text-[var(--color-foreground)]">{{ $borrowedAt->translatedFormat('d M Y') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Devolución</dt>
                                            <dd class="mt-0.5 {{ $isOverdue ? 'font-semibold text-[var(--color-destructive)]' : 'text-[var(--color-foreground)]' }}">
                                                {{ $dueAt->translatedFormat('d M Y') }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Renovaciones</dt>
                                            <dd class="mt-0.5 text-[var(--color-foreground)]">
                                                {{ $loan['renew_count'] ?? 0 }} / 3
                                                @if ($isActive)
                                                    <span class="text-[var(--color-muted-foreground)]">· {{ $renewalsLeft }} disponibles</span>
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center lg:shrink-0">
                                <div class="w-full sm:w-44">
                                    <div class="mb-1.5 flex items-center justify-between text-[10px] font-semibold uppercase tracking-wide">
                                        <span class="{{ $isOverdue ? 'text-[var(--color-destructive)]' : 'text-[var(--color-muted-foreground)]' }}">
                                            @if ($isOverdue)
                                                {{ abs($daysRemaining) }} {{ abs($daysRemaining) === 1 ? 'día de retraso' : 'días de retraso' }}
                                            @else
                                                {{ $daysRemaining }} {{ $daysRemaining === 1 ? 'día restante' : 'días restantes' }}
                                            @endif
                                        </span>
                                        <span class="text-[var(--color-muted-foreground)]">{{ $percentage }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-[var(--color-muted)]">
                                        <div class="h-full rounded-full {{ $isOverdue ? 'bg-[var(--color-destructive)]' : 'bg-[var(--color-primary)]' }}" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>

                                @if ($isActive)
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('library.loan.renew', $loan['id']) }}">
                                            @csrf
                                            <x-button type="submit" variant="ghost" size="sm" :disabled="$renewalsLeft === 0">
                                                <x-icon name="rotate-cw" class="h-4 w-4" />
                                                Renovar
                                            </x-button>
                                        </form>
                                        <form method="POST" action="{{ route('library.loan.return', $loan['id']) }}">
                                            @csrf
                                            <x-button type="submit" variant="primary" size="sm">
                                                <x-icon name="corner-down-left" class="h-4 w-4" />
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
