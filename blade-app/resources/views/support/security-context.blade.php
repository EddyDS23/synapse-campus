@extends('layouts.app')

@section('title', 'Contexto de Seguridad | Synapse')

@section('content')
<x-page>
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('support.ticket', $ticket['id'] ?? '') }}" class="mb-4 inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--color-muted-foreground)] transition hover:text-[var(--color-foreground)]">
                <x-icon name="arrow-left" class="h-3.5 w-3.5" />
                Volver al ticket
            </a>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--color-muted-foreground)]">Soporte · Seguridad</p>
            <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
                Contexto de seguridad
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
                Estado de autenticación y sesiones del usuario que reportó el ticket.
            </p>
        </div>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-card title="Referencia del ticket" class="lg:col-span-1">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Asunto</dt>
                    <dd class="mt-1 font-medium text-[var(--color-foreground)]">{{ $ticket['title'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Estado</dt>
                    <dd class="mt-1"><x-badge variant="neutral">{{ ucfirst(str_replace('_', ' ', $ticket['status'] ?? '—')) }}</x-badge></dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">ID del solicitante</dt>
                    <dd class="mt-1 inline-flex items-center gap-2 rounded-[var(--radius-sm)] border border-[var(--color-border)] bg-[var(--color-muted)] px-2.5 py-1 font-mono text-xs text-[var(--color-foreground)]">
                        <x-icon name="user" class="h-3.5 w-3.5 text-[var(--color-muted-foreground)]" />
                        {{ $ticket['requester_id'] ?? '—' }}
                    </dd>
                </div>
            </dl>
        </x-card>

        <x-card title="Auditoría de la cuenta" subtitle="Datos reportados por el servicio de autenticación." class="lg:col-span-2">
            @if (empty($securityContext))
                <x-empty-state
                    title="Contexto no disponible"
                    description="El servicio de auditoría no devolvió información para este usuario. Puede estar temporalmente fuera de línea."
                />
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-muted)]/40 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Autenticación 2FA</p>
                            <x-icon :name="$securityContext['two_factor_enabled'] ? 'lock' : 'unlock'" class="h-4 w-4 {{ $securityContext['two_factor_enabled'] ? 'text-[var(--color-success)]' : 'text-[var(--color-warning)]' }}" />
                        </div>
                        <p class="mt-2 text-lg font-semibold {{ $securityContext['two_factor_enabled'] ? 'text-[var(--color-success)]' : 'text-[var(--color-warning)]' }}">
                            {{ $securityContext['two_factor_enabled'] ? 'Activo' : 'Inactivo' }}
                        </p>
                        <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                            {{ $securityContext['two_factor_enabled']
                                ? 'La cuenta requiere un segundo factor al iniciar sesión.'
                                : 'La cuenta inicia sesión solo con contraseña.' }}
                        </p>
                    </div>

                    <div class="rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-muted)]/40 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Sesiones activas</p>
                            <x-icon name="monitor" class="h-4 w-4 text-[var(--color-muted-foreground)]" />
                        </div>
                        <p class="mt-2 font-mono text-lg font-semibold text-[var(--color-foreground)]">{{ $securityContext['active_sessions'] }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">Dispositivos con sesión vigente.</p>
                    </div>

                    <div class="rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-muted)]/40 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Último acceso</p>
                            <x-icon name="clock" class="h-4 w-4 text-[var(--color-muted-foreground)]" />
                        </div>
                        @if ($securityContext['last_login'])
                            <p class="mt-2 text-sm font-semibold text-[var(--color-foreground)]">
                                {{ \Carbon\Carbon::parse($securityContext['last_login'])->translatedFormat('d M Y') }}
                            </p>
                            <p class="mt-0.5 font-mono text-xs text-[var(--color-muted-foreground)]">
                                {{ \Carbon\Carbon::parse($securityContext['last_login'])->format('H:i:s') }}
                            </p>
                        @else
                            <p class="mt-2 text-sm text-[var(--color-muted-foreground)]">Sin registros</p>
                        @endif
                    </div>

                    <div class="rounded-[var(--radius-md)] border p-4 {{ $securityContext['account_blocked']
                        ? 'border-[var(--color-destructive)]/30 bg-[color-mix(in_srgb,var(--color-destructive)_8%,transparent)]'
                        : 'border-[var(--color-border)] bg-[var(--color-muted)]/40' }}">
                        <div class="flex items-center justify-between">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Estado de la cuenta</p>
                            <x-icon :name="$securityContext['account_blocked'] ? 'user-x' : 'user-check'" class="h-4 w-4 {{ $securityContext['account_blocked'] ? 'text-[var(--color-destructive)]' : 'text-[var(--color-success)]' }}" />
                        </div>
                        <p class="mt-2 text-lg font-semibold {{ $securityContext['account_blocked'] ? 'text-[var(--color-destructive)]' : 'text-[var(--color-success)]' }}">
                            {{ $securityContext['account_blocked'] ? 'Bloqueada' : 'Operativa' }}
                        </p>
                        <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                            {{ $securityContext['account_blocked']
                                ? 'Requiere intervención de un administrador de seguridad.'
                                : 'Sin restricciones de seguridad activas.' }}
                        </p>
                    </div>
                </div>
            @endif
        </x-card>
    </div>
</x-page>
@endsection
