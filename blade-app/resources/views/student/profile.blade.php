@extends('layouts.app')

@section('title', 'Mi Perfil | Synapse Campus')

@section('content')
@php
    $email = session('email', 'usuario@synapse.edu');
    $rawUsername = explode('@', $email)[0] ?? 'Usuario';
    $displayName = ucwords(str_replace(['.', '_', '-'], ' ', $rawUsername));
    $userId = session('user_id') ?? session('sub') ?? null;

    $roleLabels = [
        'student'        => 'Estudiante',
        'teacher'        => 'Docente',
        'librarian'      => 'Bibliotecario',
        'support_agent'  => 'Agente de soporte',
        'academic_admin' => 'Administrador académico',
        'security_admin' => 'Administrador de seguridad',
        'super_admin'    => 'Superadministrador',
    ];

    $sessionRoles = session('roles', []);
    if (is_string($sessionRoles)) {
        $sessionRoles = array_filter(array_map('trim', explode(',', $sessionRoles)));
    } elseif (!is_array($sessionRoles)) {
        $sessionRoles = [];
    }

    $hasAcademicRecord = !empty($number) || !empty($career) || !empty($semester) || !empty($status);
    $hasDebt = (bool) ($has_debt ?? false);

    $dashboardUrl = \Illuminate\Support\Facades\Route::has('dashboard')
        ? route('dashboard')
        : url('/dashboard');

    $myTicketsUrl = \Illuminate\Support\Facades\Route::has('support.my-tickets')
        ? route('support.my-tickets')
        : null;

    $createTicketUrl = \Illuminate\Support\Facades\Route::has('support.create')
        ? route('support.create')
        : null;
@endphp

<x-page>
    <x-breadcrumb :items="[
        ['label' => 'Inicio', 'url' => $dashboardUrl],
        ['label' => 'Mi perfil'],
    ]" />

    {{-- Cabecera adaptable a Light / Dark Mode --}}
    <header class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 sm:p-7 shadow-[var(--shadow-sm)] flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left min-w-0">
            <x-avatar :name="$email" size="lg" class="w-20 h-20 sm:w-24 sm:h-24 text-2xl rounded-2xl shrink-0" />

            <div class="min-w-0">
                <div class="flex items-center justify-center sm:justify-start gap-2 mb-1.5">
                    <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Cuenta Institucional</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight truncate">
                    {{ $displayName }}
                </h1>

                <p class="text-sm text-[var(--color-muted-foreground)] mt-1 truncate">{{ $email }}</p>

                @if (!empty($sessionRoles))
                    <div class="mt-3 flex flex-wrap items-center justify-center sm:justify-start gap-1.5">
                        @foreach ($sessionRoles as $roleKey)
                            <span class="inline-flex items-center rounded-lg border border-[var(--color-border)] bg-[var(--color-muted)]/70 px-2.5 py-0.5 text-xs font-semibold text-[var(--color-foreground)]">
                                {{ $roleLabels[$roleKey] ?? ucfirst(str_replace('_', ' ', $roleKey)) }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if ($createTicketUrl)
            <div class="flex justify-center sm:justify-end shrink-0">
                <x-button :href="$createTicketUrl" variant="primary" size="sm">
                    <x-icon name="message-square" class="w-4 h-4" />
                    Solicitar corrección
                </x-button>
            </div>
        @endif
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Columna izquierda (5/12): Identidad real y Estado financiero --}}
        <div class="lg:col-span-5 space-y-6">
            <section aria-labelledby="identity-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)]">
                <h2 id="identity-heading" class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)] mb-4">
                    Identidad y sesión
                </h2>

                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Correo institucional</dt>
                        <dd class="mt-1 font-semibold text-[var(--color-foreground)] break-all">{{ $email }}</dd>
                    </div>

                    @if (!empty($userId))
                        <div class="pt-3.5 border-t border-[var(--color-border)]">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Identificador de cuenta (ULID)</dt>
                            <dd class="mt-1.5 flex items-center justify-between gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-3 py-2">
                                <span id="profile-user-ulid" class="font-mono text-xs font-semibold text-[var(--color-foreground)] truncate">{{ $userId }}</span>
                                <button
                                    type="button"
                                    onclick="copyProfileUlid()"
                                    class="shrink-0 text-xs font-semibold text-[var(--color-primary)] hover:underline cursor-pointer"
                                >
                                    <span id="copy-ulid-label">Copiar</span>
                                </button>
                            </dd>
                        </div>
                    @endif

                    <div class="pt-3.5 border-t border-[var(--color-border)]">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Sesión activa</dt>
                        <dd class="mt-1.5 inline-flex items-center gap-2 text-xs font-semibold text-[var(--color-success)]">
                            <span class="h-2 w-2 rounded-full bg-[var(--color-success)]"></span>
                            Autenticado vía AuthVault
                        </dd>
                    </div>
                </dl>
            </section>

            @if ($hasAcademicRecord)
                <section aria-labelledby="financial-heading" class="rounded-2xl border bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] {{ $hasDebt ? 'border-[var(--color-destructive)]/35' : 'border-[var(--color-border)]' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 id="financial-heading" class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                                Estado financiero
                            </h2>
                            <p class="mt-1.5 text-lg font-bold {{ $hasDebt ? 'text-[var(--color-destructive)]' : 'text-[var(--color-success)]' }}">
                                {{ $hasDebt ? 'Pendiente de pago' : 'Sin adeudos' }}
                            </p>
                        </div>

                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $hasDebt ? 'bg-[var(--color-destructive)]/15 text-[var(--color-destructive)]' : 'bg-[var(--color-success)]/15 text-[var(--color-success)]' }}">
                            <x-icon :name="$hasDebt ? 'alert-circle' : 'check'" class="h-5 w-5" />
                        </span>
                    </div>

                    <p class="mt-3 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                        {{ $hasDebt
                            ? 'Tienes trámites o cuotas pendientes en tesorería. Acude a ventanilla para regularizar tu situación.'
                            : 'Tu cuenta se encuentra al corriente con los servicios institucionales.' }}
                    </p>
                </section>
            @endif
        </div>

        {{-- Columna derecha (7/12): Expediente académico y accesos reales --}}
        <div class="lg:col-span-7 space-y-6">
            <section aria-labelledby="academic-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)]">
                <div class="flex items-center justify-between gap-2 pb-4 mb-5 border-b border-[var(--color-border)]">
                    <div>
                        <h2 id="academic-heading" class="text-base font-bold text-[var(--color-foreground)]">
                            Expediente académico
                        </h2>
                        <p class="text-xs text-[var(--color-muted-foreground)] mt-0.5">
                            Datos escolares sincronizados desde el portal estudiantil.
                        </p>
                    </div>

                    @if ($hasAcademicRecord && !empty($status))
                        <x-badge variant="success">{{ strtoupper($status) }}</x-badge>
                    @endif
                </div>

                @if ($hasAcademicRecord)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/45 p-4">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Número de control</dt>
                            <dd class="mt-1.5 font-mono text-lg font-bold tracking-wider text-[var(--color-foreground)]">
                                {{ $number ?? '—' }}
                            </dd>
                        </div>

                        <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/45 p-4">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Semestre actual</dt>
                            <dd class="mt-1.5 text-lg font-bold text-[var(--color-primary)]">
                                {{ !empty($semester) ? $semester . '° Ciclo' : '—' }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/45 p-4">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Programa educativo</dt>
                            <dd class="mt-1.5 text-sm sm:text-base font-semibold text-[var(--color-foreground)]">
                                {{ $career ?? 'No especificado' }}
                            </dd>
                        </div>
                    </dl>
                @else
                    <div class="py-3">
                        <x-empty-state
                            title="Sin expediente estudiantil activo"
                            description="Esta cuenta no tiene datos escolares asociados o el servicio académico no está disponible en este momento."
                        />
                    </div>
                @endif
            </section>

            @if ($myTicketsUrl || $createTicketUrl)
                <section aria-labelledby="support-shortcut-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 id="support-shortcut-heading" class="text-sm font-bold text-[var(--color-foreground)]">
                            ¿Necesitas actualizar tus datos?
                        </h2>
                        <p class="mt-1 text-xs text-[var(--color-muted-foreground)] leading-relaxed">
                            Los cambios de correo, matrícula o rol institucional se gestionan mediante solicitud en la Mesa de Ayuda.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        @if ($myTicketsUrl)
                            <a
                                href="{{ $myTicketsUrl }}"
                                class="inline-flex h-9 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-3.5 text-xs font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors"
                            >
                                Mis tickets
                            </a>
                        @endif
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-page>

<script>
    function copyProfileUlid() {
        const ulidText = document.getElementById('profile-user-ulid')?.innerText?.trim() || '';
        const label = document.getElementById('copy-ulid-label');

        if (navigator.clipboard && ulidText) {
            navigator.clipboard.writeText(ulidText).then(function () {
                if (label) {
                    label.textContent = 'Copiado';
                    setTimeout(function () {
                        label.textContent = 'Copiar';
                    }, 2000);
                }
            });
        }
    }
</script>
@endsection