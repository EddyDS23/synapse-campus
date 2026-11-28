@extends('layouts.app')

@section('title', 'Todos los Tickets | Synapse Campus')

@section('content')
@php
    $currentStatus   = request('status', '');
    $currentPriority = request('priority', '');
    $currentPage     = (int) ($meta['currentPage'] ?? 1);
    $totalPages      = (int) ($meta['totalPage'] ?? 1);
    $statusLabels    = [
        'open'        => 'Abierto',
        'in_progress' => 'En progreso',
        'on_hold'     => 'En espera',
        'resolved'    => 'Resuelto',
        'closed'      => 'Cerrado',
    ];
    $priorityLabels  = [
        'low'    => 'Baja',
        'medium' => 'Media',
        'high'   => 'Alta',
        'urgent' => 'Urgente',
    ];
@endphp

<x-page>
    <header class="bg-[var(--color-card)] p-6 sm:p-7 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)]">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Mesa de Ayuda · Agentes</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
            Todos los tickets
        </h1>
        <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1">
            Gestión centralizada de incidencias y solicitudes de soporte del campus.
        </p>
    </header>

    {{-- KPIs --}}
    <section aria-label="Indicadores de mesa de ayuda" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat label="Total de tickets" :value="$meta['total'] ?? count($tickets)" tone="primary" />
        <x-stat label="En proceso" :value="collect($tickets)->whereIn('status', ['open', 'in_progress'])->count()" tone="warning" />
        <x-stat label="Resueltos" :value="collect($tickets)->whereIn('status', ['resolved', 'closed'])->count()" tone="success" />
    </section>

    {{-- Tabla y filtros --}}
    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)]">
        <div class="flex flex-col gap-3 border-b border-[var(--color-border)] p-5 lg:flex-row lg:items-center lg:justify-between rounded-t-2xl">
            <h2 class="text-sm sm:text-base font-bold text-[var(--color-foreground)]">Bandeja de incidencias</h2>

            <form id="all-tickets-filter-form" method="GET" action="{{ route('support.all-tickets') }}" class="flex flex-wrap items-center gap-2.5">
                <input type="hidden" id="all-status-input" name="status" value="{{ $currentStatus }}">
                <input type="hidden" id="all-priority-input" name="priority" value="{{ $currentPriority }}">

                {{-- Dropdown Estado --}}
                <div class="relative" data-dropdown>
                    <button
                        type="button"
                        onclick="toggleAgentDropdown(this)"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        class="inline-flex h-9 min-w-[180px] items-center justify-between gap-2.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-3.5 text-xs sm:text-sm font-medium text-[var(--color-foreground)] hover:bg-[var(--color-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition-colors cursor-pointer"
                    >
                        <span>{{ $currentStatus !== '' ? ($statusLabels[strtolower($currentStatus)] ?? ucfirst($currentStatus)) : 'Todos los estados' }}</span>
                        <x-icon name="chevron-down" class="h-4 w-4 text-[var(--color-muted-foreground)] transition-transform duration-150" data-chevron />
                    </button>

                    <div role="listbox" data-menu class="hidden absolute left-0 sm:left-auto sm:right-0 z-50 mt-1.5 w-52 max-h-64 overflow-y-auto rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-1.5 shadow-[var(--shadow-lg)]">
                        <button
                            type="button"
                            role="option"
                            onclick="selectAgentFilter('all-status-input', '')"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs sm:text-sm transition-colors cursor-pointer {{ $currentStatus === '' ? 'bg-[var(--color-primary)]/10 font-semibold text-[var(--color-primary)]' : 'text-[var(--color-foreground)] hover:bg-[var(--color-muted)]' }}"
                        >
                            <span>Todos los estados</span>
                            @if ($currentStatus === '')
                                <x-icon name="check" class="h-3.5 w-3.5 text-[var(--color-primary)]" />
                            @endif
                        </button>
                        @foreach (($statuses ?? []) as $s)
                            @php $isSelected = $currentStatus === $s; @endphp
                            <button
                                type="button"
                                role="option"
                                onclick="selectAgentFilter('all-status-input', '{{ $s }}')"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs sm:text-sm transition-colors cursor-pointer {{ $isSelected ? 'bg-[var(--color-primary)]/10 font-semibold text-[var(--color-primary)]' : 'text-[var(--color-foreground)] hover:bg-[var(--color-muted)]' }}"
                            >
                                <span>{{ $statusLabels[strtolower($s)] ?? ucfirst($s) }}</span>
                                @if ($isSelected)
                                    <x-icon name="check" class="h-3.5 w-3.5 text-[var(--color-primary)]" />
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Dropdown Prioridad --}}
                <div class="relative" data-dropdown>
                    <button
                        type="button"
                        onclick="toggleAgentDropdown(this)"
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        class="inline-flex h-9 min-w-[190px] items-center justify-between gap-2.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-3.5 text-xs sm:text-sm font-medium text-[var(--color-foreground)] hover:bg-[var(--color-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition-colors cursor-pointer"
                    >
                        <span>{{ $currentPriority !== '' ? ($priorityLabels[strtolower($currentPriority)] ?? ucfirst($currentPriority)) : 'Todas las prioridades' }}</span>
                        <x-icon name="chevron-down" class="h-4 w-4 text-[var(--color-muted-foreground)] transition-transform duration-150" data-chevron />
                    </button>

                    <div role="listbox" data-menu class="hidden absolute left-0 sm:left-auto sm:right-0 z-50 mt-1.5 w-52 max-h-64 overflow-y-auto rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-1.5 shadow-[var(--shadow-lg)]">
                        <button
                            type="button"
                            role="option"
                            onclick="selectAgentFilter('all-priority-input', '')"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs sm:text-sm transition-colors cursor-pointer {{ $currentPriority === '' ? 'bg-[var(--color-primary)]/10 font-semibold text-[var(--color-primary)]' : 'text-[var(--color-foreground)] hover:bg-[var(--color-muted)]' }}"
                        >
                            <span>Todas las prioridades</span>
                            @if ($currentPriority === '')
                                <x-icon name="check" class="h-3.5 w-3.5 text-[var(--color-primary)]" />
                            @endif
                        </button>
                        @foreach (($priorities ?? []) as $p)
                            @php $isSelected = $currentPriority === $p; @endphp
                            <button
                                type="button"
                                role="option"
                                onclick="selectAgentFilter('all-priority-input', '{{ $p }}')"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs sm:text-sm transition-colors cursor-pointer {{ $isSelected ? 'bg-[var(--color-primary)]/10 font-semibold text-[var(--color-primary)]' : 'text-[var(--color-foreground)] hover:bg-[var(--color-muted)]' }}"
                            >
                                <span>{{ $priorityLabels[strtolower($p)] ?? ucfirst($p) }}</span>
                                @if ($isSelected)
                                    <x-icon name="check" class="h-3.5 w-3.5 text-[var(--color-primary)]" />
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                @if (request()->filled('status') || request()->filled('priority'))
                    <a
                        href="{{ route('support.all-tickets') }}"
                        class="inline-flex h-9 items-center rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-xs font-semibold text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors"
                    >
                        Limpiar
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto rounded-b-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[var(--color-muted)]/60 border-b border-[var(--color-border)] text-xs font-bold text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <th scope="col" class="px-6 py-3.5">Asunto / ID</th>
                        <th scope="col" class="px-6 py-3.5">Prioridad</th>
                        <th scope="col" class="px-6 py-3.5">Estado</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Gestionar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)] text-xs sm:text-sm">
                    @forelse ($tickets as $ticket)
                        @php
                            $pKey = strtolower($ticket['priority'] ?? '');
                            $pVariant = match ($pKey) {
                                'high', 'alta', 'urgent' => 'danger',
                                'medium', 'media' => 'warning',
                                default => 'success',
                            };
                            $sKey = strtolower($ticket['status'] ?? '');
                            $sVariant = match ($sKey) {
                                'open', 'abierto' => 'primary',
                                'resolved', 'resuelto', 'closed', 'cerrado' => 'success',
                                'in_progress', 'en progreso' => 'warning',
                                default => 'neutral',
                            };
                        @endphp
                        <tr class="hover:bg-[var(--color-muted)]/35 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-[var(--color-foreground)]">{{ $ticket['title'] }}</div>
                                <div class="font-mono text-[11px] text-[var(--color-muted-foreground)] mt-0.5">
                                    #TK-{{ str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$pVariant">{{ $priorityLabels[$pKey] ?? ucfirst($pKey) }}</x-badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge :variant="$sVariant">{{ $statusLabels[$sKey] ?? ucfirst($sKey) }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a
                                    href="{{ route('support.ticket', $ticket['id']) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 text-[var(--color-primary)] hover:bg-[var(--color-primary)] hover:text-white transition-colors"
                                    aria-label="Gestionar ticket {{ $ticket['title'] }}"
                                >
                                    <x-icon name="chevron-right" class="h-4 w-4" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-[var(--color-muted-foreground)]">
                                No se encontraron tickets con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (!empty($tickets) && $totalPages > 1)
        <div class="flex justify-center pt-1">
            <nav aria-label="Paginación de tickets" class="inline-flex flex-wrap items-center gap-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] px-4 py-2.5 shadow-[var(--shadow-sm)] text-xs text-[var(--color-muted-foreground)]">
                @if ($currentPage > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="inline-flex items-center gap-1 rounded-lg border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-2.5 py-1 font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors">
                        <x-icon name="chevron-left" class="w-3.5 h-3.5" />
                        Anterior
                    </a>
                @endif
                <span>
                    Página <strong class="text-[var(--color-foreground)]">{{ $currentPage }}</strong> de <strong class="text-[var(--color-foreground)]">{{ $totalPages }}</strong>
                    <span class="mx-2 text-[var(--color-border)]">•</span>
                    <strong class="text-[var(--color-primary)]">{{ $meta['total'] ?? count($tickets) }}</strong> tickets
                </span>
                @if ($currentPage < $totalPages)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="inline-flex items-center gap-1 rounded-lg border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-2.5 py-1 font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors">
                        Siguiente
                        <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                    </a>
                @endif
            </nav>
        </div>
    @endif
</x-page>

<script>
    function toggleAgentDropdown(trigger) {
        const container = trigger.closest('[data-dropdown]');
        const menu = container.querySelector('[data-menu]');
        const chevron = container.querySelector('[data-chevron]');
        const isOpen = !menu.classList.contains('hidden');

        document.querySelectorAll('[data-menu]').forEach(m => m.classList.add('hidden'));
        document.querySelectorAll('[data-chevron]').forEach(c => c.classList.remove('rotate-180'));

        if (!isOpen) {
            menu.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            trigger.setAttribute('aria-expanded', 'false');
        }
    }

    function selectAgentFilter(inputId, value) {
        document.getElementById(inputId).value = value;
        document.getElementById('all-tickets-filter-form').submit();
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('[data-dropdown]')) {
            document.querySelectorAll('[data-menu]').forEach(m => m.classList.add('hidden'));
            document.querySelectorAll('[data-chevron]').forEach(c => c.classList.remove('rotate-180'));
        }
    });
</script>
@endsection