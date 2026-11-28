@extends('layouts.app')

@section('title', 'Panel de Administración | Synapse Campus')

@section('content')
<x-page>
    <header class="rounded-2xl bg-[var(--color-chrome-bg)] border border-[var(--color-chrome-border)] p-6 sm:p-7 shadow-[var(--shadow-sm)]">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-rose-200">Administración</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">
            Panel de administración
        </h1>
        <p class="text-sm sm:text-base text-stone-300 mt-1 max-w-2xl">
            Accesos directos a la gestión de identidades, auditoría del sistema y diagnóstico de seguridad.
        </p>
    </header>

    <section aria-label="Herramientas de administración" class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <a
            href="{{ route('admin.users') }}"
            class="group rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] hover:border-[var(--color-primary)]/45 transition-all flex flex-col justify-between"
        >
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                        <x-icon name="user-check" class="h-5 w-5" />
                    </span>
                    <x-icon name="chevron-right" class="h-4 w-4 text-[var(--color-muted-foreground)] group-hover:text-[var(--color-primary)] group-hover:translate-x-0.5 transition-all" />
                </div>
                <h2 class="text-base sm:text-lg font-bold text-[var(--color-foreground)] group-hover:text-[var(--color-primary)] transition-colors">
                    Usuarios y roles
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-[var(--color-muted-foreground)] leading-relaxed">
                    Asigna o revoca roles institucionales y gestiona los permisos de acceso en el ecosistema.
                </p>
            </div>
            <div class="mt-5 pt-3 border-t border-[var(--color-border)] text-xs font-bold uppercase tracking-wider text-[var(--color-primary)]">
                Gestionar identidades
            </div>
        </a>

        <a
            href="{{ route('admin.audit') }}"
            class="group rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] hover:border-[var(--color-primary)]/45 transition-all flex flex-col justify-between"
        >
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                        <x-icon name="activity" class="h-5 w-5" />
                    </span>
                    <x-icon name="chevron-right" class="h-4 w-4 text-[var(--color-muted-foreground)] group-hover:text-[var(--color-primary)] group-hover:translate-x-0.5 transition-all" />
                </div>
                <h2 class="text-base sm:text-lg font-bold text-[var(--color-foreground)] group-hover:text-[var(--color-primary)] transition-colors">
                    Registro de auditoría
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-[var(--color-muted-foreground)] leading-relaxed">
                    Consulta la bitácora centralizada de eventos, acciones y direcciones IP registradas por AuthVault.
                </p>
            </div>
            <div class="mt-5 pt-3 border-t border-[var(--color-border)] text-xs font-bold uppercase tracking-wider text-[var(--color-primary)]">
                Explorar bitácora
            </div>
        </a>

        <a
            href="{{ route('admin.security-status') }}"
            class="group rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] hover:border-[var(--color-primary)]/45 transition-all flex flex-col justify-between"
        >
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                        <x-icon name="shield" class="h-5 w-5" />
                    </span>
                    <x-icon name="chevron-right" class="h-4 w-4 text-[var(--color-muted-foreground)] group-hover:text-[var(--color-primary)] group-hover:translate-x-0.5 transition-all" />
                </div>
                <h2 class="text-base sm:text-lg font-bold text-[var(--color-foreground)] group-hover:text-[var(--color-primary)] transition-colors">
                    Estado de seguridad
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-[var(--color-muted-foreground)] leading-relaxed">
                    Diagnostica el estado de 2FA, sesiones vigentes y posibles bloqueos de una cuenta por ULID.
                </p>
            </div>
            <div class="mt-5 pt-3 border-t border-[var(--color-border)] text-xs font-bold uppercase tracking-wider text-[var(--color-primary)]">
                Consultar cuenta
            </div>
        </a>
    </section>
</x-page>
@endsection