@extends('layouts.app')

@section('title', 'Registro de Auditoría | Synapse Campus')

@section('content')
@php
    $logList     = $logs ?? [];
    $totalEvents = (int) ($meta['total'] ?? count($logList));
    $currentPage = (int) ($meta['current_page'] ?? $meta['currentPage'] ?? 1);
    $lastPage    = (int) ($meta['last_page'] ?? $meta['totalPage'] ?? 1);
@endphp

<x-page>
    <x-breadcrumb :items="[
        ['label' => 'Administración'],
        ['label' => 'Registro de auditoría'],
    ]" />

    <header class="bg-[var(--color-card)] p-6 sm:p-7 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)] flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
                <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Administración</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
                Registro de auditoría
            </h1>
            <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1 max-w-2xl">
                Historial de acciones y eventos de seguridad registrados por AuthVault en el ecosistema.
            </p>
        </div>

        <div class="inline-flex items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-3.5 py-2 font-mono text-xs font-semibold text-[var(--color-foreground)] self-start sm:self-center shrink-0">
            <x-icon name="activity" class="h-4 w-4 text-[var(--color-primary)]" />
            <span>{{ number_format($totalEvents) }} {{ $totalEvents === 1 ? 'evento' : 'eventos' }}</span>
        </div>
    </header>

    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] overflow-hidden">
        <div class="flex items-center justify-between border-b border-[var(--color-border)] px-6 py-4">
            <h2 class="text-sm sm:text-base font-bold text-[var(--color-foreground)]">Bitácora del sistema</h2>
            <span class="text-xs text-[var(--color-muted-foreground)]">Orden cronológico descendente</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-[var(--color-muted)]/60 border-b border-[var(--color-border)] text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                        <th scope="col" class="px-6 py-3.5">Acción</th>
                        <th scope="col" class="px-6 py-3.5">Usuario</th>
                        <th scope="col" class="px-6 py-3.5">Servicio</th>
                        <th scope="col" class="px-6 py-3.5">Recurso</th>
                        <th scope="col" class="px-6 py-3.5">IP</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)] text-xs sm:text-sm">
                    @forelse ($logList as $log)
                        <tr class="transition-colors hover:bg-[var(--color-muted)]/35">
                            <td class="px-6 py-4 font-bold text-[var(--color-foreground)]">
                                {{ $log['action'] ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if (!empty($log['user_id']))
                                    <span class="font-mono text-xs text-[var(--color-muted-foreground)]">{{ $log['user_id'] }}</span>
                                @else
                                    <span class="text-xs text-[var(--color-muted-foreground)]">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if (!empty($log['service']))
                                    <x-badge variant="primary">{{ $log['service'] }}</x-badge>
                                @else
                                    <span class="text-xs text-[var(--color-muted-foreground)]">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if (!empty($log['resource_type']))
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-medium text-[var(--color-foreground)]">{{ $log['resource_type'] }}</span>
                                        @if (!empty($log['resource_id']))
                                            <span
                                                class="rounded-lg border border-[var(--color-border)] bg-[var(--color-muted)]/70 px-2 py-0.5 font-mono text-[11px] text-[var(--color-muted-foreground)]"
                                                title="{{ $log['resource_id'] }}"
                                            >
                                                {{ substr($log['resource_id'], 0, 8) }}…
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-[var(--color-muted-foreground)]">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-[var(--color-muted-foreground)] whitespace-nowrap">
                                {{ $log['ip_address'] ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-[var(--color-muted-foreground)] whitespace-nowrap">
                                {{ !empty($log['created_at']) ? \Carbon\Carbon::parse($log['created_at'])->format('d M Y, H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10">
                                <x-empty-state
                                    title="Sin registros de auditoría"
                                    description="AuthVault no devolvió eventos para mostrar en este momento."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (!empty($logList) && $lastPage > 1)
        <div class="flex justify-center pt-1">
            <nav aria-label="Paginación de auditoría" class="inline-flex flex-wrap items-center gap-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] px-4 py-2.5 shadow-[var(--shadow-sm)] text-xs text-[var(--color-muted-foreground)]">
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
                    Página <strong class="text-[var(--color-foreground)]">{{ $currentPage }}</strong> de <strong class="text-[var(--color-foreground)]">{{ $lastPage }}</strong>
                    <span class="mx-2 text-[var(--color-border)]">•</span>
                    <strong class="text-[var(--color-primary)]">{{ number_format($totalEvents) }}</strong> registros
                </span>

                @if ($currentPage < $lastPage)
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