@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page>
    <!-- Hero Banner -->
    <header class="mb-6">
        <div class="rounded-[var(--radius-xl)] border border-[var(--color-border)] bg-[var(--color-card)] p-8 md:p-10 relative overflow-hidden shadow-[var(--shadow-sm)]">
            <div class="absolute top-0 right-0 w-64 h-64 bg-[var(--color-primary)]/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-[var(--color-secondary)]/10 rounded-full -ml-10 -mb-10 blur-2xl"></div>

            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-center md:text-left">
                    <span class="inline-block px-3 py-1 bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20 text-[var(--color-primary)] rounded-full text-[10px] font-bold uppercase tracking-widest mb-3">
                        Portal Institucional Activo
                    </span>
                    <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
                        Hola, <span class="text-[var(--color-primary)]">{{ explode('@', session('email', 'Usuario'))[0] }}</span>
                    </h1>
                    <p class="text-[var(--color-muted-foreground)] text-base mt-2 max-w-md">
                        Gestiona tu vida académica desde el portal Synapse Campus.
                    </p>
                </div>

                <div class="bg-[var(--color-muted)] border border-[var(--color-border)] p-5 rounded-[var(--radius-lg)] text-center shadow-[var(--shadow-sm)]">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[var(--color-muted-foreground)] mb-1">Hoy es</p>
                    <p class="text-xl font-bold text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">{{ now()->translatedFormat('d \d\e F') }}</p>
                    <p class="text-xs font-mono text-[var(--color-primary)] mt-1">{{ now()->format('Y') }}</p>
                </div>
            </div>
        </div>
    </header>

    @if(config('app.debug'))
        <div class="mb-4 px-4 py-2 rounded-[var(--radius-md)] bg-[var(--color-muted)] text-[var(--color-muted-foreground)] text-xs font-mono">
            Roles en sesión: {{ implode(', ', session('roles', ['(vacío)'])) }}
        </div>
    @endif

    @if (RoleHelper::hasRole('student'))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-card title="Académico" subtitle="Tu información académica">
                <div class="space-y-2 mt-2">
                    <a href="{{ route('student.profile') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Mi Perfil</span>
                    </a>
                    <a href="{{ route('student.schedule') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Mi Horario</span>
                    </a>
                    <a href="{{ route('student.subjects') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Mis Materias</span>
                    </a>
                </div>
            </x-card>

            <x-card title="Biblioteca" subtitle="Recursos y préstamos">
                <div class="space-y-2 mt-2">
                    <a href="{{ route('library.books') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-success)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Catálogo</span>
                    </a>
                    <a href="{{ route('library.loans') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-success)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Mis Préstamos</span>
                    </a>
                    <a href="{{ route('library.fines') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-success)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Mis Multas</span>
                    </a>
                </div>
            </x-card>

            <x-card title="Soporte" subtitle="Mesa de ayuda">
                <div class="space-y-2 mt-2">
                    <a href="{{ route('support.my-tickets') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-accent)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Mis Tickets</span>
                    </a>
                    <a href="{{ route('support.create') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-accent)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Nuevo Ticket</span>
                    </a>
                </div>
            </x-card>
        </div>
    @endif

    @if (RoleHelper::hasRole('teacher'))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-card title="Académico" subtitle="Tu información académica">
                <div class="space-y-2 mt-2">
                    <a href="{{ route('student.schedule') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Mi Horario</span>
                    </a>
                    <a href="{{ route('student.subjects') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Mis Materias</span>
                    </a>
                </div>
            </x-card>

            <x-card title="Soporte" subtitle="Mesa de ayuda">
                <div class="space-y-2 mt-2">
                    <a href="{{ route('support.my-tickets') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-accent)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Mis Tickets</span>
                    </a>
                    <a href="{{ route('support.create') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-accent)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Nuevo Ticket</span>
                    </a>
                </div>
            </x-card>
        </div>
    @endif

    @if (RoleHelper::isLibrarian())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-card title="Biblioteca" subtitle="Gestión de acervos">
                <div class="space-y-2 mt-2">
                    <a href="{{ route('library.inventory') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-success)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Inventario</span>
                    </a>
                    <a href="{{ route('library.books') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-success)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Catálogo</span>
                    </a>
                </div>
            </x-card>
        </div>
    @endif

    @if (RoleHelper::isAgent())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-card title="Mesa de Ayuda" subtitle="Gestión de tickets">
                <div class="space-y-2 mt-2">
                    <a href="{{ route('support.all-tickets') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-accent)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5H9a2 2 0 0 0-2 2v14l5-3 5 3V7a2 2 0 0 0-2-2z"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Todos los Tickets</span>
                    </a>
                    <a href="{{ route('support.all-tickets') }}?status=open" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-accent)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Tickets Abiertos</span>
                    </a>
                </div>
            </x-card>
        </div>
    @endif

    @if (RoleHelper::hasAnyRole(['academic_admin', 'security_admin', 'super_admin']))
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @if (RoleHelper::hasAnyRole(['security_admin', 'super_admin']))
                <x-card title="Seguridad" subtitle="Diagnóstico de cuentas">
                    <div class="space-y-2 mt-2">
                        <a href="{{ route('admin.security-status') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                            <svg class="h-4 w-4 text-[var(--color-destructive)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span class="text-sm text-[var(--color-foreground)]">Estado de Seguridad</span>
                        </a>
                    </div>
                </x-card>
            @endif

            <x-card title="Administración" subtitle="Gestión del sistema">
                <div class="space-y-2 mt-2">
                    <a href="{{ route('admin.users') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Usuarios y Roles</span>
                    </a>
                    <a href="{{ route('admin.audit') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                        <svg class="h-4 w-4 text-[var(--color-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span class="text-sm text-[var(--color-foreground)]">Auditoría</span>
                    </a>
                    @if (RoleHelper::hasAnyRole(['academic_admin', 'super_admin']))
                        <a href="{{ route('library.inventory') }}" class="flex items-center gap-3 p-2 rounded-[var(--radius-md)] hover:bg-[var(--color-muted)] transition-colors">
                            <svg class="h-4 w-4 text-[var(--color-success)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/></svg>
                            <span class="text-sm text-[var(--color-foreground)]">Inventario</span>
                        </a>
                    @endif
                </div>
            </x-card>
        </div>
    @endif

    {{-- Fallback para roles no reconocidos --}}
    @if (!RoleHelper::hasRole('student') && !RoleHelper::hasRole('teacher') && !RoleHelper::isLibrarian() && !RoleHelper::isAgent() && !RoleHelper::hasAnyRole(['academic_admin', 'security_admin', 'super_admin']))
        <x-card title="Sin secciones asignadas" subtitle="Tu cuenta no tiene secciones configuradas para este portal.">
            <p class="text-sm text-[var(--color-muted-foreground)] mt-2">
                Contacta al administrador para que te asignen los roles correspondientes.
            </p>
            <div class="mt-4">
                <x-button href="{{ route('security.index') }}" variant="ghost" size="sm">Ver mi perfil de seguridad</x-button>
            </div>
        </x-card>
    @endif
</x-page>
@endsection
