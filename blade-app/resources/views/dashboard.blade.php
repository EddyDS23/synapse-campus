@extends('layouts.app')

@section('title', 'Dashboard - Synapse Campus')

@section('content')
<x-page>
    <!-- Hero Banner -->
    <header class="mb-8">
        <div class="rounded-[var(--radius-xl)] border border-[var(--color-border)] bg-[var(--color-card)] p-8 md:p-10 relative overflow-hidden shadow-[var(--shadow-sm)]">
            <div class="absolute top-0 right-0 w-64 h-64 bg-[var(--color-primary)]/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-[var(--color-accent)]/10 rounded-full -ml-10 -mb-10 blur-2xl"></div>

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

    @if (RoleHelper::hasRole('student'))
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                <x-card title="Portal Académico" subtitle="Accesos rápidos a tu información académica">
                    <div class="flex flex-wrap gap-3 mt-2">
                        <x-button href="{{ route('student.profile') }}" variant="primary" size="sm">Mi Perfil</x-button>
                        <x-button href="{{ route('student.schedule') }}" variant="ghost" size="sm">Mi Horario</x-button>
                        <x-button href="{{ route('student.subjects') }}" variant="ghost" size="sm">Mis Materias</x-button>
                    </div>
                </x-card>
            </div>

            <div class="lg:col-span-4">
                <x-card title="Biblioteca Virtual" subtitle="Recursos y préstamos">
                    <div class="space-y-3 mt-2">
                        <a href="{{ route('library.books') }}" class="flex items-center justify-between p-3 rounded-[var(--radius-md)] bg-[var(--color-muted)] border border-[var(--color-border)] hover:border-[var(--color-primary)]/40 transition-colors">
                            <span class="text-sm font-medium text-[var(--color-foreground)]">Catálogo</span>
                            <svg class="h-4 w-4 text-[var(--color-muted-foreground)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                        </a>
                        <a href="{{ route('library.loans') }}" class="flex items-center justify-between p-3 rounded-[var(--radius-md)] bg-[var(--color-muted)] border border-[var(--color-border)] hover:border-[var(--color-primary)]/40 transition-colors">
                            <span class="text-sm font-medium text-[var(--color-foreground)]">Mis Préstamos</span>
                            <svg class="h-4 w-4 text-[var(--color-muted-foreground)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                        </a>
                        <a href="{{ route('library.fines') }}" class="flex items-center justify-between p-3 rounded-[var(--radius-md)] bg-[var(--color-muted)] border border-[var(--color-border)] hover:border-[var(--color-primary)]/40 transition-colors">
                            <span class="text-sm font-medium text-[var(--color-foreground)]">Mis Multas</span>
                            <svg class="h-4 w-4 text-[var(--color-muted-foreground)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                        </a>
                    </div>
                </x-card>
            </div>

            <div class="lg:col-span-12">
                <x-card title="Centro de Ayuda y Soporte" subtitle="¿Tienes problemas técnicos o dudas académicas?">
                    <div class="flex flex-col md:flex-row items-center gap-4 mt-2">
                        <p class="text-sm text-[var(--color-muted-foreground)] flex-1">Nuestro equipo está listo para ayudarte.</p>
                        <div class="flex gap-3">
                            <x-button href="{{ route('support.my-tickets') }}" variant="ghost" size="sm">Mis Tickets</x-button>
                            <x-button href="{{ route('support.create') }}" variant="primary" size="sm">+ Abrir Ticket</x-button>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    @endif

    @if (RoleHelper::isAgent())
        <x-card title="Consola de Agente" subtitle="Mesa de ayuda Synapse">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mt-2">
                <p class="text-sm text-[var(--color-muted-foreground)]">Gestiona todos los tickets desde la sección correspondiente.</p>
                <x-button href="{{ route('support.all-tickets') }}" variant="primary" size="sm">Gestionar Tickets</x-button>
            </div>
        </x-card>
    @endif

    @if (RoleHelper::isLibrarian())
        <x-card title="Gestión de Acervos" subtitle="Administración de inventario y préstamos globales">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-2">
                <p class="text-sm text-[var(--color-muted-foreground)]">Administra el catálogo y el stock de la biblioteca.</p>
                <x-button href="{{ route('library.inventory') }}" variant="primary" size="sm">Entrar al Inventario</x-button>
            </div>
        </x-card>
    @endif
</x-page>
@endsection
