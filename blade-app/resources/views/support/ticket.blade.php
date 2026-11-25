@extends('layouts.app')

@section('title', 'Ticket: ' . $ticket['title'])

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
    $priorityLabels = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'];
    $priorityVariants = ['low' => 'neutral', 'medium' => 'accent', 'high' => 'warning', 'urgent' => 'danger'];
    $progress = match ($statusKey) {
        'resolved', 'resuelto', 'closed', 'cerrado' => 100,
        'in_progress', 'en progreso', 'on_hold', 'en espera' => 50,
        default => 15,
    };
    $comments = collect($ticket['comments'] ?? []);
@endphp

<x-page>
    <header class="mb-6">
        <a href="{{ $isAgent ? route('support.all-tickets') : route('support.my-tickets') }}" class="mb-4 inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--color-muted-foreground)] transition hover:text-[var(--color-foreground)]">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" />
            {{ $isAgent ? 'Todos los tickets' : 'Mis tickets' }}
        </a>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="font-mono text-[11px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">
                    #TK-{{ $ticket['id'] }}
                </p>
                <h1 class="mt-1 font-[family-name:var(--font-serif)] text-2xl font-semibold leading-tight text-[var(--color-foreground)] sm:text-3xl">
                    {{ $ticket['title'] }}
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-badge :variant="$statusVariants[$statusKey] ?? 'neutral'">
                    {{ $statusLabels[$statusKey] ?? ucfirst($statusKey ?: '—') }}
                </x-badge>
                <x-badge :variant="$priorityVariants[$priorityKey] ?? 'neutral'">
                    Prioridad {{ $priorityLabels[$priorityKey] ?? ucfirst($priorityKey ?: '—') }}
                </x-badge>
            </div>
        </div>
    </header>

    @if ($errors->any())
        <x-alert type="error" class="mb-6">
            <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>{{ $errors->first('error') }}</span>
        </x-alert>
    @endif

    @if (session('success'))
        <x-alert type="success" class="mb-6">
            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>{{ session('success') }}</span>
        </x-alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Detalles del reporte">
                <p class="whitespace-pre-line text-sm leading-relaxed text-[var(--color-foreground)]">{{ $ticket['description'] }}</p>

                <div class="mt-6 border-t border-[var(--color-border)] pt-4">
                    <div class="mb-2 flex items-center justify-between text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">
                        <span>Ciclo de resolución</span>
                        <span>{{ $progress }}%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-[var(--color-muted)]">
                        <div class="h-full rounded-full bg-[var(--color-primary)] transition-all" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </x-card>

            @if (!$isAgent && in_array($statusKey, ['resolved', 'resuelto'], true))
                <x-card class="border-[var(--color-warning)]/30">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold text-[var(--color-foreground)]">¿El problema persiste?</p>
                            <p class="mt-1 text-xs text-[var(--color-muted-foreground)]">Si la solución no fue efectiva, puedes reabrir el caso.</p>
                        </div>
                        <form method="POST" action="{{ route('support.reopen', $ticket['id']) }}">
                            @csrf
                            @method('PATCH')
                            <x-button type="submit" variant="accent" size="sm">Reabrir ticket</x-button>
                        </form>
                    </div>
                </x-card>
            @endif

            <section aria-labelledby="activity-heading">
                <h2 id="activity-heading" class="mb-3 flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.14em] text-[var(--color-muted-foreground)]">
                    <x-icon name="message-square" class="h-4 w-4" />
                    Actividad y comentarios
                </h2>

                @if ($comments->isEmpty())
                    <x-empty-state
                        title="Sin comentarios todavía"
                        description="Las respuestas y actualizaciones de este ticket aparecerán aquí."
                    />
                @else
                    <div class="space-y-3">
                        @foreach ($comments as $comment)
                            @php $internal = $isAgent && !empty($comment['is_internal']); @endphp
                            <article class="rounded-[var(--radius-lg)] border bg-[var(--color-card)] p-4 shadow-[var(--shadow-sm)] {{ $internal ? 'border-[var(--color-warning)]/30' : 'border-[var(--color-border)]' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <x-avatar :name="$comment['user_name'] ?? 'U'" size="sm" />
                                        <div>
                                            <p class="text-sm font-semibold text-[var(--color-foreground)]">
                                                {{ $comment['user_name'] ?? 'Usuario de la institución' }}
                                            </p>
                                            @if (!empty($comment['created_at']))
                                                <p class="text-[10px] text-[var(--color-muted-foreground)]">
                                                    {{ \Carbon\Carbon::parse($comment['created_at'])->translatedFormat('d M Y, H:i') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($internal)
                                        <x-badge variant="warning">Solo agentes</x-badge>
                                    @endif
                                </div>
                                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-[var(--color-foreground)]">{{ $comment['body'] }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif

                @if (!in_array($statusKey, ['closed', 'cerrado'], true))
                    <x-card class="mt-4">
                        <form method="POST" action="{{ route('support.comment', $ticket['id']) }}" class="space-y-3">
                            @csrf
                            <x-field label="Nuevo comentario">
                                <textarea
                                    name="body"
                                    rows="4"
                                    required
                                    placeholder="Escribe una respuesta o actualización..."
                                    class="w-full resize-y rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-4 py-3 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                                ></textarea>
                            </x-field>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                @if ($isAgent)
                                    <label class="flex cursor-pointer items-center gap-2 text-xs font-medium text-[var(--color-muted-foreground)]">
                                        <input type="checkbox" name="is_internal" value="1" class="h-4 w-4 rounded border-[var(--color-border)] accent-[var(--color-warning)]">
                                        Nota interna (visible solo para agentes)
                                    </label>
                                @else
                                    <span></span>
                                @endif
                                <x-button type="submit" variant="primary" size="sm">
                                    <x-icon name="send" class="h-3.5 w-3.5" />
                                    Enviar comentario
                                </x-button>
                            </div>
                        </form>
                    </x-card>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <x-card title="Cronología">
                <ol class="space-y-4 text-sm">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-[var(--color-primary)]"></span>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Apertura</p>
                            <p class="mt-0.5 text-[var(--color-foreground)]">
                                {{ !empty($ticket['created_at']) ? \Carbon\Carbon::parse($ticket['created_at'])->translatedFormat('d M Y, H:i') : 'Sin registro' }}
                            </p>
                        </div>
                    </li>
                    @if (!empty($ticket['resolved_at']))
                        <li class="flex gap-3">
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-[var(--color-success)]"></span>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Resolución</p>
                                <p class="mt-0.5 text-[var(--color-foreground)]">{{ \Carbon\Carbon::parse($ticket['resolved_at'])->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                        </li>
                    @endif
                    @if (!empty($ticket['closed_at']))
                        <li class="flex gap-3">
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-[var(--color-muted-foreground)]"></span>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Cierre</p>
                                <p class="mt-0.5 text-[var(--color-foreground)]">{{ \Carbon\Carbon::parse($ticket['closed_at'])->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                        </li>
                    @endif
                </ol>
            </x-card>

            @if ($isAgent)
                <x-card title="Panel de agente">
                    <div class="space-y-5">
                        <form method="POST" action="{{ route('support.assign', $ticket['id']) }}" class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <label for="assignee_id" class="block text-xs font-medium text-[var(--color-foreground)]">Asignar responsable</label>
                            <div class="flex gap-2">
                                <input
                                    id="assignee_id"
                                    type="text"
                                    name="assignee_id"
                                    placeholder="ID del agente"
                                    required
                                    class="h-9 min-w-0 flex-1 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-3 font-mono text-xs text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                                >
                                <x-button type="submit" variant="ghost" size="sm">Asignar</x-button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('support.status', $ticket['id']) }}" class="space-y-2">
                            @csrf
                            @method('PATCH')
                            <label for="status" class="block text-xs font-medium text-[var(--color-foreground)]">Cambiar estado</label>
                            <div class="flex gap-2">
                                <select
                                    id="status"
                                    name="status"
                                    class="h-9 min-w-0 flex-1 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-2 text-xs text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                                >
                                    <option value="in_progress">En progreso</option>
                                    <option value="on_hold">En espera</option>
                                    <option value="resolved">Resuelto</option>
                                    <option value="closed">Cerrado</option>
                                </select>
                                <x-button type="submit" variant="primary" size="sm">Actualizar</x-button>
                            </div>
                        </form>

                        @if (RoleHelper::isSecurityAdmin())
                            <div class="border-t border-[var(--color-border)] pt-4">
                                <a href="{{ route('support.security-context', $ticket['id']) }}" class="flex items-center justify-between rounded-[var(--radius-md)] border border-[var(--color-primary)]/20 bg-[var(--color-primary)]/5 px-3 py-2.5 text-xs font-semibold text-[var(--color-primary)] transition hover:bg-[var(--color-primary)]/10">
                                    <span class="inline-flex items-center gap-2">
                                        <x-icon name="shield" class="h-4 w-4" />
                                        Contexto de seguridad
                                    </span>
                                    <x-icon name="chevron-right" class="h-3.5 w-3.5" />
                                </a>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif
        </aside>
    </div>
</x-page>
@endsection
