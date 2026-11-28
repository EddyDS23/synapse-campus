@extends('layouts.app')

@section('title', 'Dashboard | Synapse Campus')

@section('content')
@php
    $roles = session('roles', []);
    $roleLabels = [
        'student'        => 'Estudiante',
        'teacher'        => 'Docente',
        'librarian'      => 'Bibliotecario',
        'support_agent'  => 'Agente de soporte',
        'academic_admin' => 'Administrador académico',
        'security_admin' => 'Administrador de seguridad',
        'super_admin'    => 'Superadministrador',
    ];

    $isStudent       = in_array('student', $roles, true);
    $isTeacher       = in_array('teacher', $roles, true);
    $isLibrarian     = RoleHelper::isLibrarian();
    $isAgent         = RoleHelper::isAgent();
    $isAcademicAdmin = in_array('academic_admin', $roles, true);
    $isSecurityAdmin = in_array('security_admin', $roles, true);
    $isSuperAdmin    = in_array('super_admin', $roles, true);
    $canManageUsers  = $isSecurityAdmin || $isSuperAdmin;
    $isAdmin         = $isAcademicAdmin || $canManageUsers;

    $primaryRole = null;
    foreach (['super_admin', 'security_admin', 'academic_admin', 'support_agent', 'librarian', 'teacher', 'student'] as $candidate) {
        if (in_array($candidate, $roles, true)) {
            $primaryRole = $candidate;
            break;
        }
    }

    $panelTitles = [
        'super_admin'    => 'Panel de superadministración',
        'security_admin' => 'Panel de seguridad',
        'academic_admin' => 'Panel académico',
        'support_agent'  => 'Panel de soporte',
        'librarian'      => 'Panel de biblioteca',
        'teacher'        => 'Panel docente',
        'student'        => 'Panel del estudiante',
    ];

    $panelDescriptions = [
        'super_admin'    => 'Administra usuarios y roles, audita el ecosistema y supervisa el estado de seguridad de las cuentas.',
        'security_admin' => 'Administra usuarios y roles, revisa la auditoría y diagnostica el estado de seguridad de las cuentas.',
        'academic_admin' => 'Consulta la auditoría del ecosistema y supervisa el inventario de la biblioteca.',
        'support_agent'  => 'Atiende la bandeja de tickets: asigna responsables, cambia estados y responde a los solicitantes.',
        'librarian'      => 'Administra el inventario y las existencias del acervo, y consulta el catálogo bibliotecario.',
        'teacher'        => 'Consulta tu horario y las materias asignadas, y da seguimiento a tus solicitudes de soporte.',
        'student'        => 'Consulta tu carga académica activa, revisa tus préstamos bibliotecarios y mantén protegida tu cuenta.',
    ];

    $roleBadges = [];
    foreach ($roles as $role) {
        $roleBadges[] = $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', $role));
    }

    $panelTitle       = $primaryRole ? $panelTitles[$primaryRole] : 'Panel de tu cuenta';
    $panelDescription = $primaryRole
        ? $panelDescriptions[$primaryRole]
        : (empty($roles)
            ? 'Tu cuenta aún no tiene roles asignados. Contacta al administrador para habilitar tus accesos.'
            : 'Tu cuenta tiene roles que este portal todavía no reconoce. Contacta al administrador para revisar tus accesos.');

    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Buenos días' : ($hour < 19 ? 'Buenas tardes' : 'Buenas noches');

    $primaryCta = match (true) {
        $isStudent || $isTeacher => ['label' => 'Ver Horario', 'url' => route('student.schedule'), 'icon' => 'calendar'],
        $isAgent                 => ['label' => 'Abrir Bandeja', 'url' => route('support.all-tickets'), 'icon' => 'life-buoy'],
        $isLibrarian             => ['label' => 'Inventario', 'url' => route('library.inventory'), 'icon' => 'library'],
        $canManageUsers          => ['label' => 'Gestionar Usuarios', 'url' => route('admin.users'), 'icon' => 'users'],
        $isAdmin                 => ['label' => 'Ver Auditoría', 'url' => route('admin.audit'), 'icon' => 'shield'],
        default                  => ['label' => 'Seguridad', 'url' => route('security.index'), 'icon' => 'shield'],
    };

    $modules = [];

    if ($isStudent || $isTeacher) {
        $modules[] = [
            'title'       => 'Académico',
            'description' => 'Horario semanal del ciclo, mapa curricular y datos de tu expediente.',
            'icon'        => 'graduation-cap',
            'tone'        => 'primary',
            'actions'     => [
                ['label' => 'Ver horario', 'url' => route('student.schedule'), 'variant' => 'primary'],
                ['label' => 'Mis materias', 'url' => route('student.subjects'), 'variant' => 'ghost'],
            ],
        ];
    }

    if ($isStudent) {
        $modules[] = [
            'title'       => 'Biblioteca',
            'description' => 'Catálogo del acervo, préstamos vigentes y adeudos por devoluciones.',
            'icon'        => 'book-open',
            'tone'        => 'neutral',
            'actions'     => [
                ['label' => 'Abrir catálogo', 'url' => route('library.books'), 'variant' => 'primary'],
                ['label' => 'Mis préstamos', 'url' => route('library.loans'), 'variant' => 'ghost'],
            ],
        ];
    }

    if ($isLibrarian || $isAcademicAdmin || $isSuperAdmin) {
        $modules[] = [
            'title'       => 'Biblioteca',
            'description' => 'Altas de libros, control de existencias y consulta del catálogo.',
            'icon'        => 'library',
            'tone'        => 'neutral',
            'actions'     => [
                ['label' => 'Inventario', 'url' => route('library.inventory'), 'variant' => 'primary'],
                ['label' => 'Ver catálogo', 'url' => route('library.books'), 'variant' => 'ghost'],
            ],
        ];
    }

    if ($isAgent) {
        $modules[] = [
            'title'       => 'Mesa de ayuda',
            'description' => 'Bandeja completa de tickets con filtros por estado, prioridad y responsable.',
            'icon'        => 'life-buoy',
            'tone'        => 'primary',
            'actions'     => [
                ['label' => 'Abrir bandeja', 'url' => route('support.all-tickets'), 'variant' => 'primary'],
            ],
        ];
    }

    if ($isAdmin) {
        $adminActions = [];
        if ($canManageUsers) {
            $adminActions[] = ['label' => 'Usuarios y roles', 'url' => route('admin.users'), 'variant' => 'primary'];
        }
        $adminActions[] = [
            'label'   => 'Auditoría',
            'url'     => route('admin.audit'),
            'variant' => $canManageUsers ? 'ghost' : 'primary',
        ];

        $modules[] = [
            'title'       => 'Administración',
            'description' => $canManageUsers
                ? 'Gestión de identidades, asignación de roles y registro de auditoría del ecosistema.'
                : 'Consulta el registro de auditoría del ecosistema.',
            'icon'        => 'users',
            'tone'        => 'primary',
            'actions'     => $adminActions,
        ];
    }

    if ($canManageUsers) {
        $modules[] = [
            'title'       => 'Seguridad',
            'description' => 'Diagnóstico del estado de seguridad de las cuentas institucionales.',
            'icon'        => 'shield',
            'tone'        => 'neutral',
            'actions'     => [
                ['label' => 'Abrir diagnóstico', 'url' => route('admin.security-status'), 'variant' => 'primary'],
            ],
        ];
    }

    if (!$isAgent) {
        $modules[] = [
            'title'       => 'Soporte',
            'description' => 'Reporta incidencias y da seguimiento a tus solicitudes con la mesa de ayuda.',
            'icon'        => 'ticket',
            'tone'        => 'neutral',
            'actions'     => [
                ['label' => 'Mis tickets', 'url' => route('support.my-tickets'), 'variant' => 'primary'],
                ['label' => 'Nuevo ticket', 'url' => route('support.create'), 'variant' => 'ghost'],
            ],
        ];
    }

    $moduleColumns = match (count($modules)) {
        0, 1    => 'grid-cols-1',
        2       => 'grid-cols-1 md:grid-cols-2',
        3       => 'grid-cols-1 md:grid-cols-3',
        default => 'grid-cols-1 md:grid-cols-2',
    };
    $singleModule = count($modules) === 1;

    $toneClasses = [
        'primary' => 'bg-[var(--color-primary)]/10 text-[var(--color-primary)] border-[var(--color-primary)]/20',
        'neutral' => 'bg-[var(--color-muted)] text-[var(--color-foreground)] border-[var(--color-border)]',
    ];

    $tokenExpiry = (int) session('token_expiry', 0);
    $expiresAt = $tokenExpiry > 0 ? \Carbon\Carbon::createFromTimestamp($tokenExpiry) : null;

    $ecosystemMeta = [
        'online'   => ['label' => 'En línea', 'dot' => 'bg-[var(--color-success)]', 'text' => 'text-[var(--color-success)]'],
        'degraded' => ['label' => 'Degradado', 'dot' => 'bg-[var(--color-warning)]', 'text' => 'text-[var(--color-warning)]'],
        'offline'  => ['label' => 'Sin conexión', 'dot' => 'bg-[var(--color-destructive)]', 'text' => 'text-[var(--color-destructive)]'],
    ];

    $ecosystemHealth = [];
    if ($isAdmin) {
        try {
            $ecosystemHealth = app(\App\Services\HealthService::class)->check();
        } catch (\Throwable) {
            $ecosystemHealth = [];
        }
    }
@endphp

<x-page>
    <header class="rounded-2xl border border-[var(--color-border)] border-l-4 border-l-[var(--color-primary)] bg-[var(--color-card)] p-6 sm:p-8 shadow-[var(--shadow-sm)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
        <div class="min-w-0">
            <time datetime="{{ now()->toDateString() }}" class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-[var(--color-primary)] mb-1.5">
                <x-icon name="calendar" class="h-3.5 w-3.5" />
                {{ ucfirst(now()->translatedFormat('l, d \d\e F Y')) }}
            </time>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[var(--color-foreground)]">
                {{ $greeting }} · {{ $panelTitle }}
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
                {{ $panelDescription }}
            </p>
        </div>

        <x-button :href="$primaryCta['url']" variant="primary" size="md" class="shrink-0">
            <x-icon :name="$primaryCta['icon']" class="h-4 w-4" />
            {{ $primaryCta['label'] }}
        </x-button>
    </header>

    @if (empty($roles))
        <x-alert type="warning">
            <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>No hay roles asociados a esta sesión. Algunas secciones permanecerán ocultas hasta que un administrador te asigne uno.</span>
        </x-alert>
    @endif

    @if (count($modules))
        <section aria-labelledby="workspace-heading" class="space-y-3.5">
            <div class="flex items-center justify-between">
                <h2 id="workspace-heading" class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                    Tus espacios de trabajo
                </h2>
                <span class="text-xs text-[var(--color-muted-foreground)]">{{ count($modules) }} módulos disponibles</span>
            </div>

            <div class="grid {{ $moduleColumns }} gap-5">
                @foreach ($modules as $module)
                    <article class="flex {{ $singleModule ? 'flex-col sm:flex-row sm:items-center sm:justify-between' : 'flex-col' }} rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] transition-all hover:border-[var(--color-primary)]/40 hover:shadow-[var(--shadow-md)]">
                        <div class="flex items-start gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border {{ $toneClasses[$module['tone']] ?? $toneClasses['primary'] }}">
                                <x-icon :name="$module['icon']" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold leading-snug text-[var(--color-card-foreground)]">
                                    {{ $module['title'] }}
                                </h3>
                                <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                                    {{ $module['description'] }}
                                </p>
                            </div>
                        </div>

                        <div class="{{ $singleModule ? 'mt-4 flex shrink-0 flex-wrap items-center gap-2.5 border-t border-[var(--color-border)] pt-4 sm:mt-0 sm:ml-6 sm:border-l sm:border-t-0 sm:pl-6 sm:pt-0' : 'mt-5 flex flex-wrap items-center gap-2.5 border-t border-[var(--color-border)] pt-4' }}">
                            @foreach ($module['actions'] as $action)
                                <x-button :href="$action['url']" :variant="$action['variant']" size="sm">
                                    {{ $action['label'] }}
                                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                </x-button>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section aria-labelledby="account-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3.5">
                <x-avatar :name="session('email', 'U')" size="md" />
                <div class="min-w-0">
                    <h2 id="account-heading" class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                        Sesión institucional
                    </h2>
                    <p class="truncate text-sm font-bold text-[var(--color-card-foreground)] mt-0.5">
                        {{ session('email', '—') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-x-7 gap-y-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                        Roles asignados
                    </p>
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        @forelse ($roleBadges as $label)
                            <x-badge variant="neutral">{{ $label }}</x-badge>
                        @empty
                            <x-badge variant="warn">Sin rol</x-badge>
                        @endforelse
                    </div>
                </div>

                @if ($expiresAt)
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                            Acceso válido hasta
                        </p>
                        <p class="mt-1.5 inline-flex items-center gap-1.5 font-mono text-xs font-semibold text-[var(--color-card-foreground)]">
                            <x-icon name="clock" class="h-3.5 w-3.5 text-[var(--color-primary)]" />
                            {{ $expiresAt->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-2.5">
                    @if ($isStudent)
                        <x-button href="{{ route('student.profile') }}" variant="ghost" size="sm">
                            Mi perfil
                        </x-button>
                    @endif
                    <x-button href="{{ route('security.index') }}" variant="primary" size="sm">
                        <x-icon name="shield" class="h-3.5 w-3.5" />
                        Seguridad
                    </x-button>
                </div>
            </div>
        </div>
    </section>

    @if ($isAdmin)
        <section aria-labelledby="ecosystem-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)]">
            <div class="flex items-start justify-between gap-3 border-b border-[var(--color-border)] px-6 py-4">
                <div>
                    <h2 id="ecosystem-heading" class="text-sm font-bold text-[var(--color-card-foreground)]">
                        Estado del ecosistema
                    </h2>
                    <p class="mt-0.5 text-xs text-[var(--color-muted-foreground)]">
                        Última verificación automática de los servicios conectados.
                    </p>
                </div>
                <x-icon name="server" class="h-5 w-5 shrink-0 text-[var(--color-muted-foreground)]" />
            </div>

            <ul class="grid gap-3 p-6 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($ecosystemHealth as $service => $status)
                    @php $meta = $ecosystemMeta[$status] ?? ['label' => 'Desconocido', 'dot' => 'bg-[var(--color-muted-foreground)]', 'text' => 'text-[var(--color-muted-foreground)]']; @endphp
                    <li class="flex items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-3.5 py-2.5">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $meta['dot'] }}" aria-hidden="true"></span>
                        <span class="text-xs font-semibold text-[var(--color-card-foreground)] truncate">{{ $service }}</span>
                        <span class="ml-auto text-[10px] font-bold uppercase tracking-wide {{ $meta['text'] }}">{{ $meta['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-page>
@endsection