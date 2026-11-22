@extends('layouts.app')

@section('title', 'Mis Tickets | Synapse Support')

@section('content')
<x-page>
    <header class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">Centro de <span class="text-[var(--color-primary)]">Soporte</span></h1>
            <p class="text-[var(--color-muted-foreground)] mt-1">Gestiona tus consultas técnicas y reportes de incidencia.</p>
        </div>
        <x-button href="{{ route('support.create') }}" variant="primary" size="sm">+ Nuevo Ticket</x-button>
    </header>

    @if ($errors->any())
        <x-alert type="error" class="mb-4">{{ $errors->first('error') }}</x-alert>
    @endif
    @if (session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-stat label="Total Registrados" :value="$meta['total'] ?? 0" tone="primary" />
        <x-stat label="En Proceso" :value="collect($tickets)->whereIn('status', ['open','in_progress','Pending'])->count()" tone="warning" />
        <x-stat label="Resueltos" :value="collect($tickets)->whereIn('status', ['resolved','closed','Resolved','Closed'])->count()" tone="success" />
    </div>

    <!-- Filtros -->
    <section class="mb-6">
        <x-card padding="p-4">
            <form method="GET" action="{{ route('support.my-tickets') }}" class="flex flex-wrap items-center gap-3">
                <select name="status" class="h-10 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] text-sm">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <x-button type="submit" variant="primary" size="sm">Filtrar</x-button>
                <a href="{{ route('support.my-tickets') }}" class="inline-flex items-center justify-center h-9 px-3 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] text-sm hover:bg-[var(--color-muted)]">Limpiar</a>
            </form>
        </x-card>
    </section>

    <!-- Tabla -->
    <x-card padding="p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] font-bold text-[var(--color-muted-foreground)] uppercase tracking-widest border-b border-[var(--color-border)]">
                        <th class="px-6 py-4">Asunto / ID</th>
                        <th class="px-6 py-4">Prioridad</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Cronología</th>
                        <th class="px-6 py-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)]">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-[var(--color-muted)]/40 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-[var(--color-foreground)] text-sm">{{ $ticket['title'] }}</div>
                                <div class="text-[10px] text-[var(--color-muted-foreground)] font-mono mt-0.5">#TK-{{ str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $pColor = match(strtolower($ticket['priority'])) {
                                        'high', 'alta', 'urgent' => 'danger',
                                        'medium', 'media' => 'warning',
                                        default => 'success'
                                    };
                                @endphp
                                <x-badge :variant="$pColor">{{ ucfirst($ticket['priority']) }}</x-badge>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $sVariant = match(strtolower($ticket['status'])) {
                                        'open', 'abierto' => 'primary',
                                        'resolved', 'resuelto', 'closed', 'cerrado' => 'success',
                                        'in_progress', 'en progreso' => 'warning',
                                        default => 'neutral'
                                    };
                                @endphp
                                <x-badge :variant="$sVariant">{{ $ticket['status'] }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-xs text-[var(--color-muted-foreground)]">
                                <div>Resuelto: {{ $ticket['resolved_at'] ? \Carbon\Carbon::parse($ticket['resolved_at'])->format('d M, Y') : 'Pendiente' }}</div>
                                <div>Cerrado: {{ $ticket['closed_at'] ? \Carbon\Carbon::parse($ticket['closed_at'])->format('d M, Y') : '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('support.ticket', $ticket['id']) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-[var(--radius-md)] bg-[var(--color-muted)] text-[var(--color-primary)] hover:bg-[var(--color-primary)] hover:text-[var(--color-on-primary)] transition-all" aria-label="Ver ticket">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-[var(--color-muted-foreground)]">No se encontraron tickets.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(!empty($tickets))
            <div class="p-4 border-t border-[var(--color-border)] text-sm text-[var(--color-muted-foreground)] flex justify-between">
                <span>Página {{ $meta['currentPage'] ?? 1 }} de {{ $meta['totalPage'] ?? 1 }}</span>
                <span class="text-[var(--color-primary)] font-bold">{{ $meta['total'] ?? 0 }} tickets en total</span>
            </div>
        @endif
    </x-card>
</x-page>
@endsection
