@extends('layouts.app')

@section('title', 'Usuarios y Roles | Synapse')

@section('content')
@php
    $roleLabels = [
        'student'        => 'Estudiante',
        'teacher'        => 'Docente',
        'librarian'      => 'Bibliotecario',
        'support_agent'  => 'Agente de soporte',
        'academic_admin' => 'Administrador académico',
        'security_admin' => 'Administrador de seguridad',
        'super_admin'    => 'Superadministrador',
    ];
@endphp

<x-page>
    <header class="mb-8">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--color-muted-foreground)]">Administración</p>
        <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
            Usuarios y roles
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
            Asigna o revoca roles institucionales y consulta el estado de seguridad de una cuenta.
        </p>
    </header>

    @if ($errors->any())
        <x-alert type="error" class="mb-6">{{ $errors->first() }}</x-alert>
    @endif

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="Asignar rol" subtitle="Agrega un rol a un usuario existente.">
            <form method="POST" action="{{ route('admin.roles.assign') }}" class="flex h-full flex-col gap-4">
                @csrf

                <x-field label="ULID del usuario" :error="$errors->first('user_id')">
                    <input
                        type="text"
                        name="user_id"
                        value="{{ old('user_id') }}"
                        required
                        placeholder="01kvmbb1km2r6mmma5bd5x790n"
                        class="h-11 w-full rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-4 font-mono text-sm text-[var(--color-foreground)] placeholder:text-[var(--color-muted-foreground)]/60 focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </x-field>

                <x-field label="Rol" :error="$errors->first('role')">
                    <select
                        name="role"
                        required
                        class="h-11 w-full rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                    >
                        <option value="">Seleccionar rol…</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>{{ $roleLabels[$role] ?? $role }}</option>
                        @endforeach
                    </select>
                </x-field>

                <div class="mt-auto pt-2">
                    <x-button type="submit" variant="primary" size="md" class="w-full">
                        <x-icon name="user-check" class="h-4 w-4" />
                        Asignar rol
                    </x-button>
                </div>
            </form>
        </x-card>

        <x-card title="Revocar rol" subtitle="Elimina un rol asociado a un usuario.">
            <form method="POST" action="" id="revokeForm" class="flex h-full flex-col gap-4">
                @csrf
                @method('DELETE')

                <x-field label="ULID del usuario">
                    <input
                        type="text"
                        id="revokeUserId"
                        required
                        placeholder="01kvmbb1km2r6mmma5bd5x790n"
                        class="h-11 w-full rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-4 font-mono text-sm text-[var(--color-foreground)] placeholder:text-[var(--color-muted-foreground)]/60 focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </x-field>

                <x-field label="Rol">
                    <select
                        id="revokeRole"
                        required
                        class="h-11 w-full rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                    >
                        <option value="">Seleccionar rol…</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}">{{ $roleLabels[$role] ?? $role }}</option>
                        @endforeach
                    </select>
                </x-field>

                <div class="mt-auto pt-2">
                    <x-button type="button" variant="danger" size="md" class="w-full" onclick="submitRevoke()">
                        <x-icon name="user-x" class="h-4 w-4" />
                        Revocar rol
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>

    <x-card title="Estado de seguridad de una cuenta" subtitle="Consulta por ULID el estado de autenticación y las sesiones del usuario.">
        <form method="GET" action="{{ route('admin.security-status') }}" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:flex-1">
                <label for="securityUserId" class="mb-1.5 block text-xs font-medium text-[var(--color-foreground)]">ULID del usuario</label>
                <input
                    id="securityUserId"
                    type="text"
                    name="user_id"
                    value="{{ request('user_id') }}"
                    placeholder="01kvmbb1km2r6mmma5bd5x790n"
                    class="h-11 w-full rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-4 font-mono text-sm text-[var(--color-foreground)] placeholder:text-[var(--color-muted-foreground)]/60 focus:border-[var(--color-primary)] focus:outline-none"
                >
            </div>
            <x-button type="submit" variant="ghost" size="md" class="sm:w-auto">
                <x-icon name="search" class="h-4 w-4" />
                Consultar
            </x-button>
        </form>

        @if (isset($data) && $data)
            <div class="rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-muted)]/40 p-4">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">ID del usuario</dt>
                        <dd class="mt-1 truncate font-mono text-xs text-[var(--color-foreground)]">{{ $data['user_id'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Autenticación 2FA</dt>
                        <dd class="mt-1">
                            <x-badge :variant="$data['two_factor_enabled'] ? 'success' : 'neutral'">
                                {{ $data['two_factor_enabled'] ? 'Activa' : 'Inactiva' }}
                            </x-badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Estado de la cuenta</dt>
                        <dd class="mt-1">
                            <x-badge :variant="$data['account_blocked'] ? 'danger' : 'success'">
                                {{ $data['account_blocked'] ? 'Bloqueada' : 'Activa' }}
                            </x-badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Sesiones activas</dt>
                        <dd class="mt-1 font-mono text-lg font-semibold text-[var(--color-foreground)]">{{ $data['active_sessions'] }}</dd>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Último acceso</dt>
                        <dd class="mt-1 text-sm text-[var(--color-foreground)]">
                            {{ $data['last_login'] ? \Carbon\Carbon::parse($data['last_login'])->translatedFormat('d M Y, H:i') : 'Sin registros' }}
                        </dd>
                    </div>
                </dl>
            </div>
        @elseif (request()->filled('user_id'))
            <x-empty-state
                title="Sin datos para ese usuario"
                description="El servicio de identidad no devolvió información para el ULID consultado."
            />
        @endif
    </x-card>
</x-page>
@endsection

<script>
    function submitRevoke() {
        const userId = document.getElementById('revokeUserId').value.trim();
        const role = document.getElementById('revokeRole').value;
        const form = document.getElementById('revokeForm');

        if (!userId || !role) {
            form.reportValidity();
            return;
        }

        form.action = '/admin/users/' + encodeURIComponent(userId) + '/roles/' + encodeURIComponent(role);
        form.submit();
    }
</script>
