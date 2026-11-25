@extends('layouts.app')

@section('title', 'Auditoría | Synapse')

@section('content')
<x-page>
    <header class="mb-8">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--color-muted-foreground)]">Administración</p>
        <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
            Registro de auditoría
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
            Historial de acciones y eventos registrados por AuthVault en el ecosistema.
        </p>
    </header>

    <x-card padding="p-0">
        <div class="flex flex-col gap-2 border-b border-[var(--color-border)] p-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-semibold text-[var(--color-foreground)]">Bitácora del sistema</h2>
            @if (isset($meta['total']))
                <span class="inline-flex items-center gap-1.5 rounded-full border border-[var(--color-border)] bg-[var(--color-muted)] px-3 py-1 font-mono text-xs text-[var(--color-muted-foreground)]">
                    <x-icon name="activity" class="h-3.5 w-3.5" />
                    {{ $meta['total'] }} eventos
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-[var(--color-border)] text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--color-muted-foreground)]">
                        <th scope="col" class="px-5 py-3">Acción</th>
                        <th scope="col" class="px-5 py-3">Usuario</th>
                        <th scope="col" class="px-5 py-3">Servicio</th>
                        <th scope="col" class="px-5 py-3">Recurso</th>
                        <th scope="col" class="px-5 py-3">IP</th>
                        <th scope="col" class="px-5 py-3 text-right">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)]">
                    @forelse ($logs as $log)
                        <tr class="transition-colors hover:bg-[var(--color-muted)]/40">
                            <td class="px-5 py-3 text-sm font-semibold text-[var(--color-foreground)]">{{ $log['action'] }}</td>
                            <td class="px-5 py-3">
                                @if (!empty($log['user_id']))
                                    <span class="font-mono text-xs text-[var(--color-muted-foreground)]">{{ $log['user_id'] }}</span>
                                @else
                                    <span class="text-xs text-[var(--color-muted-foreground)]">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if (!empty($log['service']))
                                    <x-badge variant="primary">{{ $log['service'] }}</x-badge>
                                @else
                                    <span class="text-xs text-[var(--color-muted-foreground)]">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if (!empty($log['resource_type']))
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-[var(--color-foreground)]">{{ $log['resource_type'] }}</span>
                                        @if (!empty($log['resource_id']))
                                            <span class="rounded border border-[var(--color-border)] bg-[var(--color-muted)] px-1.5 py-0.5 font-mono text-[10px] text-[var(--color-muted-foreground)]">
                                                {{ substr($log['resource_id'], 0, 8) }}…
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-[var(--color-muted-foreground)]">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-[var(--color-muted-foreground)]">{{ $log['ip_address'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-xs text-[var(--color-muted-foreground)]">
                                {{ !empty($log['created_at']) ? \Carbon\Carbon::parse($log['created_at'])->format('d M Y, H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6">
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

        @if (isset($meta['current_page'], $meta['last_page']))
            <div class="flex flex-col gap-2 border-t border-[var(--color-border)] px-5 py-3 text-xs text-[var(--color-muted-foreground)] sm:flex-row sm:items-center sm:justify-between">
                <span>Página {{ $meta['current_page'] }} de {{ $meta['last_page'] }}</span>
                <span>{{ $meta['total'] ?? count($logs) }} registros en total</span>
            </div>
        @endif
    </x-card>
</x-page>
@endsection
