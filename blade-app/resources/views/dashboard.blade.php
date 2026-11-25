@extends('layouts.app')

@section('title', 'Dashboard')

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
        'student'        => 'Consulta tu información académica, tus préstamos y el estado de tus solicitudes de soporte.',
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

    $modules = [];

    if ($isStudent || $isTeacher) {
        $modules[] = [
            'title'       => 'Académico',
            'description' => 'Horario del ciclo, materias inscritas y datos de tu perfil académico.',
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
            'tone'        => 'secondary',
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
            'tone'        => 'secondary',
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
            'tone'        => 'accent',
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
            'tone'        => 'primary',
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
            'tone'        => 'accent',
            'actions'     => [
                ['label' => 'Mis tickets', 'url' => route('support.my-tickets'), 'variant' => 'primary'],
                ['label' => 'Nuevo ticket', 'url' => route('support.create'), 'variant' => 'ghost'],
            ],
        ];
    }

    $moduleColumns = match (count($modules)) {
        0 => 'grid-cols-1',
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-3',
        default => 'grid-cols-1 md:grid-cols-2',
    };
    $singleModule = count($modules) === 1;

    $toneClasses = [
        'primary'   => 'bg-[var(--color-primary)]/10 text-[var(--color-primary)] border-[var(--color-primary)]/15',
        'secondary' => 'bg-[var(--color-secondary)]/10 text-[var(--color-secondary)] border-[var(--color-secondary)]/15',
        'accent'    => 'bg-[var(--color-accent)]/10 text-[var(--color-accent)] border-[var(--color-accent)]/15',
    ];

    $tokenExpiry = (int) session('token_expiry', 0);
    $expiresAt = $tokenExpiry > 0 ? \Carbon\Carbon::createFromTimestamp($tokenExpiry) : null;

    $ecosystemMeta = [
        'online'   => ['label' => 'En línea', 'dot' => 'bg-[var(--color-success)]', 'text' => 'text-[var(--color-success)]'],
        'degraded' => ['label' => 'Degradado', 'dot' => 'bg-[var(--color-warning)]', 'text' => 'text-[var(--color-warning)]'],
        'offline'  => ['label' => 'Sin conexión', 'dot' => 'bg-[var(--color-destructive)]', 'text' => 'text-[var(--color-destructive)]'],
    ];

    // El layout ya consulta este estado (cacheado 30 s); lo reutilizamos aquí
    // para pintarlo en el dashboard sin duplicar peticiones.
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
    {{-- Encabezado: saludo y contexto real de la cuenta --}}
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--color-muted-foreground)]">
                {{ $greeting }}
            </p>
            <h1 class="mt-2 font-[family-name:var(--font-serif)] text-3xl font-semibold leading-tight tracking-tight text-[var(--color-foreground)]">
                {{ $panelTitle }}
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
                {{ $panelDescription }}
            </p>
        </div>

        <time datetime="{{ now()->toDateString() }}"
            class="inline-flex shrink-0 items-center gap-2 self-start rounded-full border border-[var(--color-border)] bg-[var(--color-card)] px-3.5 py-1.5 text-xs font-medium text-[var(--color-muted-foreground)] shadow-[var(--shadow-sm)] sm:self-auto">
            <x-icon name="calendar" class="h-3.5 w-3.5" />
            {{ ucfirst(now()->translatedFormat('l, d \d\e F \d\e Y')) }}
        </time>
    </header>

    @if (empty($roles))
        <x-alert type="warning" class="mb-6">
            <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>No hay roles asociados a esta sesión. Algunas secciones permanecerán ocultas hasta que un administrador te asigne uno.</span>
        </x-alert>
    @endif

    {{-- Espacios de trabajo: un acceso por módulo, sin repetir el menú lateral --}}
    @if (count($modules))
        <section aria-labelledby="workspace-heading">
            <h2 id="workspace-heading" class="mb-3 font-[family-name:var(--font-serif)] text-lg font-semibold text-[var(--color-foreground)]">
                Tus espacios de trabajo
            </h2>

            <div class="grid {{ $moduleColumns }} gap-4">
                @foreach ($modules as $module)
                    <article class="flex {{ $singleModule ? 'flex-col sm:flex-row sm:items-center sm:justify-between' : 'flex-col' }} rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)] transition hover:border-[var(--color-primary)]/30 hover:shadow-[var(--shadow-md)]">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--radius-md)] border {{ $toneClasses[$module['tone']] ?? $toneClasses['primary'] }}">
                                <x-icon :name="$module['icon']" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-[family-name:var(--font-serif)] text-lg font-semibold leading-tight text-[var(--color-card-foreground)]">
                                    {{ $module['title'] }}
                                </h3>
                                <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                                    {{ $module['description'] }}
                                </p>
                            </div>
                        </div>

                        <div class="{{ $singleModule ? 'mt-4 flex shrink-0 flex-wrap items-center gap-2 border-t border-[var(--color-border)] pt-4 sm:mt-0 sm:ml-6 sm:border-l sm:border-t-0 sm:pl-6 sm:pt-0' : 'mt-5 flex flex-wrap items-center gap-2 border-t border-[var(--color-border)] pt-4' }}">
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

    {{-- Datos reales de la sesión --}}
    <section aria-labelledby="account-heading" class="mt-6 rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)]">
        <div class="flex flex-col gap-5 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <x-avatar :name="session('email', 'U')" size="md" />
                <div class="min-w-0">
                    <h2 id="account-heading" class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--color-muted-foreground)]">
                        Sesión institucional
                    </h2>
                    <p class="truncate text-sm font-semibold text-[var(--color-card-foreground)]">
                        {{ session('email', '—') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-x-7 gap-y-4">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--color-muted-foreground)]">
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
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[var(--color-muted-foreground)]">
                            Acceso válido hasta
                        </p>
                        <p class="mt-1.5 inline-flex items-center gap-1.5 text-sm font-medium text-[var(--color-card-foreground)]">
                            <x-icon name="clock" class="h-3.5 w-3.5 text-[var(--color-muted-foreground)]" />
                            {{ $expiresAt->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-2">
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

    {{-- Estado real del ecosistema (solo administradores) --}}
    @if ($isAdmin)
        <section aria-labelledby="ecosystem-heading" class="mt-6 rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)]">
            <div class="flex items-start justify-between gap-3 border-b border-[var(--color-border)] px-5 py-4">
                <div>
                    <h2 id="ecosystem-heading" class="font-[family-name:var(--font-serif)] text-lg font-semibold text-[var(--color-card-foreground)]">
                        Estado del ecosistema
                    </h2>
                    <p class="mt-1 text-xs text-[var(--color-muted-foreground)]">
                        Última verificación automática de los servicios conectados.
                    </p>
                </div>
                <x-icon name="server" class="h-5 w-5 shrink-0 text-[var(--color-muted-foreground)]" />
            </div>

            <ul class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($ecosystemHealth as $service => $status)
                    @php $meta = $ecosystemMeta[$status] ?? ['label' => 'Desconocido', 'dot' => 'bg-[var(--color-muted-foreground)]', 'text' => 'text-[var(--color-muted-foreground)]']; @endphp
                    <li class="flex items-center gap-2 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-muted)]/40 px-3 py-2">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $meta['dot'] }}" aria-hidden="true"></span>
                        <span class="text-xs font-medium text-[var(--color-card-foreground)]">{{ $service }}</span>
                        <span class="ml-auto text-[10px] font-semibold uppercase tracking-wide {{ $meta['text'] }}">{{ $meta['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-page>
@endsection
