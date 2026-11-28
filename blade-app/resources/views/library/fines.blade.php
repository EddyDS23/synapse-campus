@extends('layouts.app')

@section('title', 'Mis Multas | Synapse Campus')

@section('content')
@php
    $pendingFines = collect($fines)->filter(fn ($fine) => ($fine['status'] ?? null) === 'pending');
    $paidFines = collect($fines)->filter(fn ($fine) => ($fine['status'] ?? null) === 'paid');
    $totalPending = $pendingFines->sum('amount');
    $totalPaid = $paidFines->sum('amount');
    $totalPages = (int) ($meta['totalPage'] ?? 1);
    $isPaginated = $totalPages > 1;
    $currentStatus = request('status', '');
    $statusLabels = [
        '' => 'Todos los estados',
        'pending' => 'Pendientes',
        'paid' => 'Pagadas',
    ];
@endphp

<x-page>
    <header class="bg-[var(--color-card)] p-6 sm:p-7 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)]">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Biblioteca</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
            Mis multas
        </h1>
        <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1">
            Revisa los cargos generados por devoluciones tardías y liquida los que estén pendientes.
        </p>
    </header>

    <section aria-label="Resumen de multas" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
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

    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-[var(--color-border)] p-5 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm sm:text-base font-bold text-[var(--color-foreground)]">Historial de cargos</h2>

            <form id="fines-filter-form" method="GET" action="{{ route('library.fines') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" id="status-input" name="status" value="{{ $currentStatus }}">

                <div class="relative" id="status-dropdown">
                    <button
                        type="button"
                        id="status-trigger"
                        onclick="toggleStatusDropdown()"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        class="inline-flex h-9 min-w-[175px] items-center justify-between gap-2.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-3.5 text-xs sm:text-sm font-medium text-[var(--color-foreground)] hover:bg-[var(--color-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition-colors cursor-pointer"
                    >
                        <span>{{ $statusLabels[$currentStatus] ?? 'Todos los estados' }}</span>
                        <x-icon name="chevron-down" class="h-4 w-4 text-[var(--color-muted-foreground)] transition-transform duration-150" id="status-chevron" />
                    </button>

                    <div
                        id="status-menu"
                        role="listbox"
                        class="hidden absolute right-0 z-30 mt-1.5 w-48 origin-top-right rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-1.5 shadow-[var(--shadow-lg)]"
                    >
                        @foreach ($statusLabels as $value => $label)
                            @php $isSelected = $currentStatus === $value; @endphp
                            <button
                                type="button"
                                role="option"
                                aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                onclick="selectFineStatus('{{ $value }}')"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs sm:text-sm transition-colors cursor-pointer {{ $isSelected ? 'bg-[var(--color-primary)]/10 font-semibold text-[var(--color-primary)]' : 'text-[var(--color-foreground)] hover:bg-[var(--color-muted)]' }}"
                            >
                                <span>{{ $label }}</span>
                                @if ($isSelected)
                                    <x-icon name="check" class="h-3.5 w-3.5 text-[var(--color-primary)]" />
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                @if (request()->filled('status'))
                    <a
                        href="{{ route('library.fines') }}"
                        class="inline-flex h-9 items-center rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-xs font-semibold text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors"
                    >
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
                        <tr class="bg-[var(--color-muted)]/60 border-b border-[var(--color-border)] text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                            <th scope="col" class="px-6 py-3.5">Monto</th>
                            <th scope="col" class="px-6 py-3.5">Estado</th>
                            <th scope="col" class="px-6 py-3.5">Cronología</th>
                            <th scope="col" class="px-6 py-3.5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-border)] text-xs sm:text-sm">
                        @foreach ($fines as $fine)
                            @php $isPending = ($fine['status'] ?? null) === 'pending'; @endphp
                            <tr class="transition-colors hover:bg-[var(--color-muted)]/35">
                                <td class="px-6 py-4 font-mono text-base font-bold text-[var(--color-foreground)]">
                                    ${{ number_format($fine['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :variant="$isPending ? 'danger' : 'success'">
                                        {{ $isPending ? 'Pendiente' : 'Pagada' }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-xs text-[var(--color-muted-foreground)]">
                                    <div>
                                        <span class="font-bold uppercase tracking-wider text-[var(--color-foreground)]">Generada:</span>
                                        {{ $fine['created_at'] ? \Carbon\Carbon::parse($fine['created_at'])->format('d/m/Y') : '—' }}
                                    </div>
                                    @if ($fine['paid_at'])
                                        <div class="mt-0.5 text-[var(--color-success)]">
                                            <span class="font-bold uppercase tracking-wider">Pagada:</span>
                                            {{ \Carbon\Carbon::parse($fine['paid_at'])->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($isPending)
                                        <form method="POST" action="{{ route('library.fine.pay', $fine['id']) }}">
                                            @csrf
                                            <x-button type="submit" variant="primary" size="sm">
                                                <x-icon name="credit-card" class="h-3.5 w-3.5" />
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

            <div class="flex items-center justify-between border-t border-[var(--color-border)] px-6 py-3.5 text-xs text-[var(--color-muted-foreground)]">
                <span>Página <strong class="text-[var(--color-foreground)]">{{ $meta['currentPage'] ?? 1 }}</strong> de <strong class="text-[var(--color-foreground)]">{{ $totalPages }}</strong></span>
                <span>{{ $meta['total'] ?? count($fines) }} registros</span>
            </div>
        @endif
    </div>
</x-page>

<script>
    function toggleStatusDropdown() {
        const menu = document.getElementById('status-menu');
        const trigger = document.getElementById('status-trigger');
        const chevron = document.getElementById('status-chevron');
        const isOpen = !menu.classList.contains('hidden');

        menu.classList.toggle('hidden', isOpen);
        trigger.setAttribute('aria-expanded', String(!isOpen));
        if (chevron) {
            chevron.classList.toggle('rotate-180', !isOpen);
        }
    }

    function selectFineStatus(value) {
        document.getElementById('status-input').value = value;
        document.getElementById('fines-filter-form').submit();
    }

    document.addEventListener('click', function (event) {
        const dropdown = document.getElementById('status-dropdown');
        const menu = document.getElementById('status-menu');
        const trigger = document.getElementById('status-trigger');
        const chevron = document.getElementById('status-chevron');

        if (dropdown && !dropdown.contains(event.target) && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
            if (chevron) chevron.classList.remove('rotate-180');
        }
    });
</script>
@endsection
