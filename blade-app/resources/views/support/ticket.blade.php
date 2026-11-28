@extends('layouts.app')

@section('title', 'Ticket #TK-' . str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) . ' | Synapse Campus')

@section('content')
@php
    $statusKey = strtolower($ticket['status'] ?? '');
    $statusLabels = [
        'open' => 'Abierto', 'abierto' => 'Abierto',
        'in_progress' => 'En progreso', 'en progreso' => 'En progreso',
        'on_hold' => 'En espera', 'en espera' => 'En espera',
        'resolved' => 'Resuelto', 'resuelto' => 'Resuelto',
        'closed' => 'Cerrado', 'cerrado' => 'Cerrado',
    ];
    $statusVariants = [
        'open' => 'primary', 'abierto' => 'primary',
        'in_progress' => 'warning', 'en progreso' => 'warning',
        'on_hold' => 'neutral', 'en espera' => 'neutral',
        'resolved' => 'success', 'resuelto' => 'success',
        'closed' => 'neutral', 'cerrado' => 'neutral',
    ];
    $priorityKey = strtolower($ticket['priority'] ?? '');
    $priorityLabels = [
        'low' => 'Baja',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
    ];
    $priorityVariants = [
        'low' => 'neutral',
        'medium' => 'warning',
        'high' => 'warning',
        'urgent' => 'danger',
    ];
    $progress = match ($statusKey) {
        'resolved', 'resuelto', 'closed', 'cerrado' => 100,
        'in_progress', 'en progreso', 'on_hold', 'en espera' => 50,
        default => 15,
    };
    $comments = collect($ticket['comments'] ?? []);
    $agentStatuses = [
        'in_progress' => 'En progreso',
        'on_hold'     => 'En espera',
        'resolved'    => 'Resuelto',
        'closed'      => 'Cerrado',
    ];
    $defaultAgentStatus = array_key_exists($statusKey, $agentStatuses) ? $statusKey : 'in_progress';
@endphp

<x-page>
    <x-breadcrumb :items="[
        ['label' => 'Soporte', 'url' => $isAgent ? route('support.all-tickets') : route('support.my-tickets')],
        ['label' => $isAgent ? 'Todos los tickets' : 'Mis tickets', 'url' => $isAgent ? route('support.all-tickets') : route('support.my-tickets')],
        ['label' => '#TK-' . str_pad($ticket['id'], 5, '0', STR_PAD_LEFT)],
    ]" />

    {{-- Cabecera Carbon (30% secundario) --}}
    <header class="rounded-2xl bg-[var(--color-chrome-bg)] border border-[var(--color-chrome-border)] p-6 sm:p-7 shadow-[var(--shadow-sm)] flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="flex items-center gap-2 mb-1.5">
                <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
                <span class="font-mono text-xs font-bold uppercase tracking-widest text-rose-200">
                    #TK-{{ str_pad($ticket['id'], 5, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight leading-snug">
                {{ $ticket['title'] }}
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <x-badge :variant="$statusVariants[$statusKey] ?? 'neutral'">
                {{ $statusLabels[$statusKey] ?? ucfirst($statusKey ?: '—') }}
            </x-badge>
            <x-badge :variant="$priorityVariants[$priorityKey] ?? 'neutral'">
                Prioridad {{ $priorityLabels[$priorityKey] ?? ucfirst($priorityKey ?: '—') }}
            </x-badge>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 items-start">
        {{-- Columna principal (2/3) --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- 1. Detalles del reporte --}}
            <section aria-labelledby="report-details-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)]">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <h2 id="report-details-heading" class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                        Detalles del reporte
                    </h2>
                    @if (!empty($ticket['category_id']))
                        <span class="font-mono text-[11px] font-semibold px-2.5 py-0.5 rounded-lg border border-[var(--color-border)] bg-[var(--color-muted)]/60 text-[var(--color-muted-foreground)]">
                            {{ $ticket['category_id'] }}
                        </span>
                    @endif
                </div>

                <p class="whitespace-pre-line text-sm sm:text-base leading-relaxed text-[var(--color-foreground)]">
                    {{ $ticket['description'] }}
                </p>

                <div class="mt-6 border-t border-[var(--color-border)] pt-4">
                    <div class="mb-2 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                        <span>Ciclo de resolución</span>
                        <span class="font-mono text-[var(--color-foreground)]">{{ $progress }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-[var(--color-muted)]">
                        <div class="h-full rounded-full bg-[var(--color-primary)] transition-all" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </section>

            {{-- Banner de reapertura (solo solicitante cuando está resuelto) --}}
            @if (!$isAgent && in_array($statusKey, ['resolved', 'resuelto'], true))
                <div class="rounded-2xl border border-[var(--color-warning)]/40 bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)] flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold text-[var(--color-foreground)]">¿El problema persiste?</p>
                        <p class="mt-0.5 text-xs text-[var(--color-muted-foreground)]">Si la solución no fue efectiva, puedes reabrir el caso para que soporte lo revise.</p>
                    </div>
                    <form method="POST" action="{{ route('support.reopen', $ticket['id']) }}" class="shrink-0">
                        @csrf
                        @method('PATCH')
                        <x-button type="submit" variant="primary" size="sm">Reabrir ticket</x-button>
                    </form>
                </div>
            @endif

            {{-- 2. Conversación: hilo con scroll independiente + caja fija fuera del scroll --}}
            <section aria-labelledby="activity-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] flex flex-col">
                {{-- Encabezado fijo de conversación --}}
                <div class="flex items-center justify-between gap-3 border-b border-[var(--color-border)] px-6 py-4">
                    <h2 id="activity-heading" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--color-foreground)]">
                        <x-icon name="message-square" class="h-4 w-4 text-[var(--color-primary)]" />
                        Actividad y comentarios
                    </h2>
                    <span class="inline-flex items-center rounded-lg border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-2.5 py-0.5 font-mono text-xs font-semibold text-[var(--color-muted-foreground)]">
                        {{ $comments->count() }} {{ $comments->count() === 1 ? 'mensaje' : 'mensajes' }}
                    </span>
                </div>

                {{-- Área con scroll exclusivo para los comentarios --}}
                <div id="comments-scroll-box" class="max-h-[420px] overflow-y-auto p-5 sm:p-6 space-y-3.5 bg-[var(--color-muted)]/20">
                    @if ($comments->isEmpty())
                        <div class="py-6">
                            <x-empty-state
                                title="Sin comentarios todavía"
                                description="Las respuestas y actualizaciones de este ticket aparecerán aquí."
                            />
                        </div>
                    @else
                        @foreach ($comments as $comment)
                            @php $internal = $isAgent && !empty($comment['is_internal']); @endphp
                            <article class="rounded-xl border p-4 shadow-[var(--shadow-sm)] {{ $internal ? 'border-[var(--color-warning)]/45 bg-[var(--color-warning)]/10' : 'border-[var(--color-border)] bg-[var(--color-card)]' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <x-avatar :name="$comment['user_name'] ?? 'U'" size="sm" />
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-[var(--color-foreground)]">
                                                {{ $comment['user_name'] ?? 'Usuario de la institución' }}
                                            </p>
                                            @if (!empty($comment['created_at']))
                                                <p class="text-[11px] text-[var(--color-muted-foreground)]">
                                                    {{ \Carbon\Carbon::parse($comment['created_at'])->translatedFormat('d M Y, H:i') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($internal)
                                        <x-badge variant="warning" class="shrink-0">Nota interna</x-badge>
                                    @endif
                                </div>
                                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-[var(--color-foreground)]">{{ $comment['body'] }}</p>
                            </article>
                        @endforeach
                    @endif
                </div>

                {{-- Formulario de nuevo comentario fijo fuera del scroll --}}
                @if (!in_array($statusKey, ['closed', 'cerrado'], true))
                    <div class="border-t border-[var(--color-border)] bg-[var(--color-card)] p-5 sm:p-6 rounded-b-2xl">
                        <form method="POST" action="{{ route('support.comment', $ticket['id']) }}" class="space-y-3.5">
                            @csrf
                            <x-field label="Escribir respuesta">
                                <textarea
                                    name="body"
                                    rows="3"
                                    required
                                    placeholder="Escribe una respuesta o actualización para el seguimiento..."
                                    class="w-full resize-y rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 py-3 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                                ></textarea>
                            </x-field>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                @if ($isAgent)
                                    <label class="flex cursor-pointer items-center gap-2 text-xs font-medium text-[var(--color-muted-foreground)] select-none">
                                        <input type="checkbox" name="is_internal" value="1" class="h-4 w-4 rounded border-[var(--color-border)] accent-[var(--color-warning)]">
                                        Nota interna (visible solo para agentes)
                                    </label>
                                @else
                                    <span></span>
                                @endif
                                <x-button type="submit" variant="primary" size="sm" class="self-end sm:self-auto">
                                    <x-icon name="send" class="h-3.5 w-3.5" />
                                    Enviar comentario
                                </x-button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="border-t border-[var(--color-border)] bg-[var(--color-muted)]/40 px-6 py-3.5 text-xs text-[var(--color-muted-foreground)] rounded-b-2xl">
                        Este ticket se encuentra cerrado y no admite nuevos comentarios.
                    </div>
                @endif
            </section>
        </div>

        {{-- Columna lateral (1/3) --}}
        <aside class="space-y-6">
            {{-- Cronología --}}
            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)]">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)] mb-4">
                    Cronología
                </h2>
                <ol class="relative space-y-4 border-l-2 border-[var(--color-border)] ml-1.5 pl-4 text-sm">
                    <li class="relative">
                        <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full bg-[var(--color-primary)] ring-4 ring-[var(--color-card)]"></span>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Apertura</p>
                        <p class="mt-0.5 font-medium text-[var(--color-foreground)]">
                            {{ !empty($ticket['created_at']) ? \Carbon\Carbon::parse($ticket['created_at'])->translatedFormat('d M Y, H:i') : 'Sin registro' }}
                        </p>
                    </li>

                    @if (!empty($ticket['resolved_at']))
                        <li class="relative">
                            <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full bg-[var(--color-success)] ring-4 ring-[var(--color-card)]"></span>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Resolución</p>
                            <p class="mt-0.5 font-medium text-[var(--color-foreground)]">
                                {{ \Carbon\Carbon::parse($ticket['resolved_at'])->translatedFormat('d M Y, H:i') }}
                            </p>
                        </li>
                    @endif

                    @if (!empty($ticket['closed_at']))
                        <li class="relative">
                            <span class="absolute -left-[21px] top-1.5 h-2.5 w-2.5 rounded-full bg-[var(--color-muted-foreground)] ring-4 ring-[var(--color-card)]"></span>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Cierre</p>
                            <p class="mt-0.5 font-medium text-[var(--color-foreground)]">
                                {{ \Carbon\Carbon::parse($ticket['closed_at'])->translatedFormat('d M Y, H:i') }}
                            </p>
                        </li>
                    @endif
                </ol>
            </div>

            {{-- Panel de gestión para agentes --}}
            @if ($isAgent)
                <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] space-y-5">
                    <div class="flex items-center gap-2">
                        <span class="w-4 h-1 rounded-full bg-[var(--color-primary)]"></span>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--color-foreground)]">
                            Panel de agente
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('support.assign', $ticket['id']) }}" class="space-y-2">
                        @csrf
                        @method('PATCH')
                        <label for="assignee_id" class="block text-xs font-semibold text-[var(--color-foreground)]">Asignar responsable</label>
                        <div class="flex gap-2">
                            <input
                                id="assignee_id"
                                type="text"
                                name="assignee_id"
                                value="{{ $ticket['assignee_id'] ?? '' }}"
                                placeholder="ID del agente"
                                required
                                class="h-9 min-w-0 flex-1 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-3 font-mono text-xs text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                            >
                            <x-button type="submit" variant="ghost" size="sm">Asignar</x-button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('support.status', $ticket['id']) }}" class="space-y-2 pt-3 border-t border-[var(--color-border)]">
                        @csrf
                        @method('PATCH')
                        <label class="block text-xs font-semibold text-[var(--color-foreground)]">Cambiar estado</label>
                        <div class="flex gap-2">
                            <input type="hidden" id="agent-status-input" name="status" value="{{ $defaultAgentStatus }}">

                            <div class="relative min-w-0 flex-1" id="agent-status-dropdown">
                                <button
                                    type="button"
                                    id="agent-status-trigger"
                                    onclick="toggleAgentTicketStatus()"
                                    aria-haspopup="listbox"
                                    aria-expanded="false"
                                    class="flex h-9 w-full items-center justify-between gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-3 text-xs font-medium text-[var(--color-foreground)] hover:bg-[var(--color-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] cursor-pointer"
                                >
                                    <span id="agent-status-label" class="truncate">{{ $agentStatuses[$defaultAgentStatus] }}</span>
                                    <x-icon name="chevron-down" id="agent-status-chevron" class="h-3.5 w-3.5 shrink-0 text-[var(--color-muted-foreground)] transition-transform duration-150" />
                                </button>

                                <div
                                    id="agent-status-menu"
                                    role="listbox"
                                    class="hidden absolute left-0 right-0 z-50 mt-1.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-1.5 shadow-[var(--shadow-lg)]"
                                >
                                    @foreach ($agentStatuses as $val => $label)
                                        @php $isSelected = $defaultAgentStatus === $val; @endphp
                                        <button
                                            type="button"
                                            role="option"
                                            aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                            data-value="{{ $val }}"
                                            data-label="{{ $label }}"
                                            onclick="selectAgentTicketStatus(this)"
                                            class="agent-status-opt flex w-full items-center justify-between rounded-lg px-3 py-1.5 text-left text-xs transition-colors cursor-pointer {{ $isSelected ? 'bg-[var(--color-primary)]/10 font-semibold text-[var(--color-primary)]' : 'text-[var(--color-foreground)] hover:bg-[var(--color-muted)]' }}"
                                        >
                                            <span>{{ $label }}</span>
                                            <x-icon name="check" class="agent-status-check h-3 w-3 text-[var(--color-primary)] {{ $isSelected ? '' : 'hidden' }}" />
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <x-button type="submit" variant="primary" size="sm">Actualizar</x-button>
                        </div>
                    </form>

                    @if (RoleHelper::isSecurityAdmin())
                        <div class="border-t border-[var(--color-border)] pt-4">
                            <a
                                href="{{ route('support.security-context', $ticket['id']) }}"
                                class="flex items-center justify-between rounded-xl border border-[var(--color-primary)]/25 bg-[var(--color-primary)]/10 px-3.5 py-2.5 text-xs font-bold text-[var(--color-primary)] transition hover:bg-[var(--color-primary)]/15"
                            >
                                <span class="inline-flex items-center gap-2">
                                    <x-icon name="shield" class="h-4 w-4" />
                                    Contexto de seguridad
                                </span>
                                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </aside>
    </div>
</x-page>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scrollBox = document.getElementById('comments-scroll-box');
        if (scrollBox) {
            scrollBox.scrollTop = scrollBox.scrollHeight;
        }
    });

    function toggleAgentTicketStatus() {
        const menu = document.getElementById('agent-status-menu');
        const trigger = document.getElementById('agent-status-trigger');
        const chevron = document.getElementById('agent-status-chevron');
        if (!menu || !trigger) return;

        const isOpen = !menu.classList.contains('hidden');
        menu.classList.toggle('hidden', isOpen);
        trigger.setAttribute('aria-expanded', String(!isOpen));
        if (chevron) chevron.classList.toggle('rotate-180', !isOpen);
    }

    function selectAgentTicketStatus(btn) {
        document.getElementById('agent-status-input').value = btn.dataset.value;
        document.getElementById('agent-status-label').textContent = btn.dataset.label;

        document.querySelectorAll('.agent-status-opt').forEach(function (el) {
            el.classList.remove('bg-[var(--color-primary)]/10', 'font-semibold', 'text-[var(--color-primary)]');
            el.classList.add('text-[var(--color-foreground)]');
            el.setAttribute('aria-selected', 'false');
            const icon = el.querySelector('.agent-status-check');
            if (icon) icon.classList.add('hidden');
        });

        btn.classList.add('bg-[var(--color-primary)]/10', 'font-semibold', 'text-[var(--color-primary)]');
        btn.classList.remove('text-[var(--color-foreground)]');
        btn.setAttribute('aria-selected', 'true');
        const activeIcon = btn.querySelector('.agent-status-check');
        if (activeIcon) activeIcon.classList.remove('hidden');

        toggleAgentTicketStatus();
    }

    document.addEventListener('click', function (e) {
        const dropdown = document.getElementById('agent-status-dropdown');
        const menu = document.getElementById('agent-status-menu');
        if (dropdown && menu && !dropdown.contains(e.target) && !menu.classList.contains('hidden')) {
            toggleAgentTicketStatus();
        }
    });
</script>
@endsection