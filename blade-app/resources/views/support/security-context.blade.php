@extends('layouts.app')

@section('title', 'Contexto de Seguridad | Synapse Campus')

@section('content')
@php
    $ticketId = $ticket['id'] ?? '';
    $formattedTicketId = $ticketId !== '' ? '#TK-' . str_pad((string) $ticketId, 5, '0', STR_PAD_LEFT) : 'Ticket';

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

    $hasContext = !empty($securityContext) && is_array($securityContext);
    $twoFactor  = !empty($securityContext['two_factor_enabled']);
    $sessions   = (int) ($securityContext['active_sessions'] ?? 0);
    $lastLogin  = $securityContext['last_login'] ?? null;
    $isBlocked  = !empty($securityContext['account_blocked']);
@endphp

<x-page>
    <x-breadcrumb :items="[
        ['label' => 'Soporte', 'url' => route('support.all-tickets')],
        ['label' => $formattedTicketId, 'url' => route('support.ticket', $ticketId)],
        ['label' => 'Contexto de seguridad'],
    ]" />

    <header class="bg-[var(--color-card)] p-6 sm:p-7 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)] flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
                <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Mesa de Ayuda · Seguridad</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
                Contexto de seguridad
            </h1>
            <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1 max-w-2xl">
                Estado de autenticación, doble factor y sesiones activas del solicitante del ticket.
            </p>
        </div>

        @if ($ticketId !== '')
            <a
                href="{{ route('support.ticket', $ticketId) }}"
                class="inline-flex h-9 items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-3.5 text-xs font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors self-start sm:self-center shrink-0"
            >
                <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                Volver al ticket
            </a>
        @endif
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 items-start">
        {{-- Columna lateral: referencia del ticket --}}
        <aside class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] lg:col-span-1">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)] mb-4">
                Referencia del ticket
            </h2>

            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Folio</dt>
                    <dd class="mt-1 font-mono text-xs font-bold text-[var(--color-primary)]">
                        {{ $formattedTicketId }}
                    </dd>
                </div>

                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Asunto</dt>
                    <dd class="mt-1 font-semibold text-[var(--color-foreground)] leading-snug">
                        {{ $ticket['title'] ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Estado del caso</dt>
                    <dd class="mt-1.5">
                        <x-badge :variant="$statusVariants[$statusKey] ?? 'neutral'">
                            {{ $statusLabels[$statusKey] ?? ucfirst(str_replace('_', ' ', $statusKey ?: '—')) }}
                        </x-badge>
                    </dd>
                </div>

                <div class="pt-3 border-t border-[var(--color-border)]">
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">ID del solicitante</dt>
                    <dd class="mt-1.5 inline-flex items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-3 py-1.5 font-mono text-xs font-semibold text-[var(--color-foreground)]">
                        <x-icon name="user" class="h-3.5 w-3.5 text-[var(--color-primary)]" />
                        {{ $ticket['requester_id'] ?? '—' }}
                    </dd>
                </div>
            </dl>
        </aside>

        {{-- Columna principal: auditoría de la cuenta --}}
        <section aria-labelledby="account-audit-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] lg:col-span-2">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-4 mb-5 border-b border-[var(--color-border)]">
                <div>
                    <h2 id="account-audit-heading" class="text-sm sm:text-base font-bold text-[var(--color-foreground)]">
                        Auditoría de la cuenta
                    </h2>
                    <p class="text-xs text-[var(--color-muted-foreground)] mt-0.5">
                        Datos reportados por el servicio de autenticación institucional.
                    </p>
                </div>

                @if ($hasContext)
                    <x-badge :variant="$isBlocked ? 'danger' : 'success'">
                        {{ $isBlocked ? 'Cuenta restringida' : 'Cuenta verificada' }}
                    </x-badge>
                @endif
            </div>

            @if (!$hasContext)
                <div class="py-4">
                    <x-empty-state
                        title="Contexto no disponible"
                        description="El servicio de auditoría no devolvió información para este usuario. Puede estar temporalmente fuera de línea."
                    />
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Autenticación 2FA --}}
                    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/45 p-5 flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Autenticación 2FA</p>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg {{ $twoFactor ? 'bg-[var(--color-success)]/15 text-[var(--color-success)]' : 'bg-[var(--color-warning)]/15 text-[var(--color-warning)]' }}">
                                <x-icon :name="$twoFactor ? 'lock' : 'unlock'" class="h-4 w-4" />
                            </span>
                        </div>
                        <div class="mt-3">
                            <p class="text-xl font-bold {{ $twoFactor ? 'text-[var(--color-success)]' : 'text-[var(--color-warning)]' }}">
                                {{ $twoFactor ? 'Activo' : 'Inactivo' }}
                            </p>
                            <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                                {{ $twoFactor
                                    ? 'La cuenta requiere un segundo factor al iniciar sesión.'
                                    : 'La cuenta inicia sesión únicamente con contraseña.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Sesiones activas --}}
                    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/45 p-5 flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Sesiones activas</p>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                                <x-icon name="monitor" class="h-4 w-4" />
                            </span>
                        </div>
                        <div class="mt-3">
                            <p class="font-mono text-2xl font-bold text-[var(--color-foreground)]">{{ $sessions }}</p>
                            <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                                {{ $sessions === 1 ? 'Dispositivo con sesión vigente.' : 'Dispositivos con sesión vigente.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Último acceso --}}
                    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/45 p-5 flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Último acceso</p>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-muted)] text-[var(--color-muted-foreground)]">
                                <x-icon name="clock" class="h-4 w-4" />
                            </span>
                        </div>
                        <div class="mt-3">
                            @if ($lastLogin)
                                <p class="text-base font-bold text-[var(--color-foreground)]">
                                    {{ \Carbon\Carbon::parse($lastLogin)->translatedFormat('d M Y') }}
                                </p>
                                <p class="mt-0.5 font-mono text-xs text-[var(--color-muted-foreground)]">
                                    {{ \Carbon\Carbon::parse($lastLogin)->format('H:i:s') }} hrs
                                </p>
                            @else
                                <p class="text-sm font-semibold text-[var(--color-muted-foreground)]">Sin registros</p>
                                <p class="mt-1 text-xs text-[var(--color-muted-foreground)]">No consta actividad reciente.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Estado de la cuenta --}}
                    <div class="rounded-xl border p-5 flex flex-col justify-between {{ $isBlocked
                        ? 'border-[var(--color-destructive)]/35 bg-[var(--color-destructive)]/10'
                        : 'border-[var(--color-border)] bg-[var(--color-muted)]/45' }}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Estado de la cuenta</p>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg {{ $isBlocked ? 'bg-[var(--color-destructive)]/15 text-[var(--color-destructive)]' : 'bg-[var(--color-success)]/15 text-[var(--color-success)]' }}">
                                <x-icon :name="$isBlocked ? 'user-x' : 'user-check'" class="h-4 w-4" />
                            </span>
                        </div>
                        <div class="mt-3">
                            <p class="text-xl font-bold {{ $isBlocked ? 'text-[var(--color-destructive)]' : 'text-[var(--color-success)]' }}">
                                {{ $isBlocked ? 'Bloqueada' : 'Operativa' }}
                            </p>
                            <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                                {{ $isBlocked
                                    ? 'Requiere intervención de un administrador de seguridad.'
                                    : 'Sin restricciones de seguridad activas.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-page>
@endsection