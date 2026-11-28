@extends('layouts.app')

@section('title', 'Usuarios y Roles | Synapse Campus')

@section('content')
@php
    $roleList = $roles ?? [];
    $roleLabels = [
        'student'        => 'Estudiante',
        'teacher'        => 'Docente',
        'librarian'      => 'Bibliotecario',
        'support_agent'  => 'Agente de soporte',
        'academic_admin' => 'Administrador académico',
        'security_admin' => 'Administrador de seguridad',
        'super_admin'    => 'Superadministrador',
    ];
    $oldAssignRole = old('role', '');
    $hasSecurityData = !empty($data) && is_array($data);
@endphp

<x-page>
    <x-breadcrumb :items="[
        ['label' => 'Administración'],
        ['label' => 'Usuarios y roles'],
    ]" />

    <header class="bg-[var(--color-card)] p-6 sm:p-7 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)]">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Administración</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
            Usuarios y roles
        </h1>
        <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1 max-w-2xl">
            Asigna o revoca roles institucionales y consulta el estado de seguridad de una cuenta.
        </p>
    </header>

    {{-- Formularios de Asignación y Revocación --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 items-stretch">
        {{-- Asignar rol --}}
        <section aria-labelledby="assign-role-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] flex flex-col">
            <div class="mb-5">
                <h2 id="assign-role-heading" class="text-base font-bold text-[var(--color-foreground)]">Asignar rol</h2>
                <p class="text-xs text-[var(--color-muted-foreground)] mt-0.5">Agrega un rol institucional a un usuario existente.</p>
            </div>

            <form method="POST" action="{{ route('admin.roles.assign') }}" class="flex flex-1 flex-col gap-4">
                @csrf

                <x-field label="ULID del usuario" :error="$errors->first('user_id')">
                    <input
                        type="text"
                        name="user_id"
                        value="{{ old('user_id') }}"
                        required
                        placeholder="01kvmbb1km2r6mmma5bd5x790n"
                        class="h-11 w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 font-mono text-sm text-[var(--color-foreground)] placeholder:text-[var(--color-muted-foreground)]/60 focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </x-field>

                <x-field label="Rol institucional" :error="$errors->first('role')">
                    <input type="hidden" id="assign-role-input" name="role" value="{{ $oldAssignRole }}">

                    <div class="relative" data-role-dropdown>
                        <button
                            type="button"
                            onclick="toggleAdminRoleDropdown(this)"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            class="flex h-11 w-full items-center justify-between gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 text-left text-sm font-medium text-[var(--color-foreground)] hover:bg-[var(--color-muted)] focus:border-[var(--color-primary)] focus:outline-none transition-colors cursor-pointer"
                        >
                            <span data-role-label class="{{ $oldAssignRole !== '' ? 'text-[var(--color-foreground)]' : 'text-[var(--color-muted-foreground)]' }}">
                                {{ $oldAssignRole !== '' ? ($roleLabels[$oldAssignRole] ?? $oldAssignRole) : 'Seleccionar rol…' }}
                            </span>
                            <x-icon name="chevron-down" data-role-chevron class="h-4 w-4 shrink-0 text-[var(--color-muted-foreground)] transition-transform duration-150" />
                        </button>

                        <div
                            role="listbox"
                            data-role-menu
                            class="hidden absolute left-0 right-0 z-50 mt-1.5 max-h-60 overflow-y-auto rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-1.5 shadow-[var(--shadow-lg)]"
                        >
                            @foreach ($roleList as $role)
                                @php $isSelected = $oldAssignRole === $role; @endphp
                                <button
                                    type="button"
                                    role="option"
                                    aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                    data-value="{{ $role }}"
                                    data-label="{{ $roleLabels[$role] ?? $role }}"
                                    onclick="selectAdminRole(this, 'assign-role-input')"
                                    class="role-option flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs sm:text-sm transition-colors cursor-pointer {{ $isSelected ? 'bg-[var(--color-primary)]/10 font-semibold text-[var(--color-primary)]' : 'text-[var(--color-foreground)] hover:bg-[var(--color-muted)]' }}"
                                >
                                    <span>{{ $roleLabels[$role] ?? $role }}</span>
                                    <x-icon name="check" class="role-check h-3.5 w-3.5 text-[var(--color-primary)] {{ $isSelected ? '' : 'hidden' }}" />
                                </button>
                            @endforeach
                        </div>
                    </div>
                </x-field>

                <div class="mt-auto pt-3">
                    <x-button type="submit" variant="primary" size="md" class="w-full">
                        <x-icon name="user-check" class="h-4 w-4" />
                        Asignar rol
                    </x-button>
                </div>
            </form>
        </section>

        {{-- Revocar rol --}}
        <section aria-labelledby="revoke-role-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] flex flex-col">
            <div class="mb-5">
                <h2 id="revoke-role-heading" class="text-base font-bold text-[var(--color-foreground)]">Revocar rol</h2>
                <p class="text-xs text-[var(--color-muted-foreground)] mt-0.5">Elimina un rol previamente asociado a una cuenta.</p>
            </div>

            <form method="POST" action="" id="revokeForm" class="flex flex-1 flex-col gap-4">
                @csrf
                @method('DELETE')

                <x-field label="ULID del usuario">
                    <input
                        type="text"
                        id="revokeUserId"
                        required
                        placeholder="01kvmbb1km2r6mmma5bd5x790n"
                        class="h-11 w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 font-mono text-sm text-[var(--color-foreground)] placeholder:text-[var(--color-muted-foreground)]/60 focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </x-field>

                <x-field label="Rol a revocar">
                    <input type="hidden" id="revoke-role-input" value="">

                    <div class="relative" data-role-dropdown>
                        <button
                            type="button"
                            id="revokeRoleTrigger"
                            onclick="toggleAdminRoleDropdown(this)"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                            class="flex h-11 w-full items-center justify-between gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 text-left text-sm font-medium text-[var(--color-foreground)] hover:bg-[var(--color-muted)] focus:border-[var(--color-primary)] focus:outline-none transition-colors cursor-pointer"
                        >
                            <span data-role-label class="text-[var(--color-muted-foreground)]">Seleccionar rol…</span>
                            <x-icon name="chevron-down" data-role-chevron class="h-4 w-4 shrink-0 text-[var(--color-muted-foreground)] transition-transform duration-150" />
                        </button>

                        <div
                            role="listbox"
                            data-role-menu
                            class="hidden absolute left-0 right-0 z-50 mt-1.5 max-h-60 overflow-y-auto rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-1.5 shadow-[var(--shadow-lg)]"
                        >
                            @foreach ($roleList as $role)
                                <button
                                    type="button"
                                    role="option"
                                    aria-selected="false"
                                    data-value="{{ $role }}"
                                    data-label="{{ $roleLabels[$role] ?? $role }}"
                                    onclick="selectAdminRole(this, 'revoke-role-input')"
                                    class="role-option flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs sm:text-sm text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors cursor-pointer"
                                >
                                    <span>{{ $roleLabels[$role] ?? $role }}</span>
                                    <x-icon name="check" class="role-check hidden h-3.5 w-3.5 text-[var(--color-primary)]" />
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <p id="revokeRoleError" class="hidden mt-1.5 text-xs font-medium text-[var(--color-destructive)]">
                        Selecciona el rol que deseas revocar.
                    </p>
                </x-field>

                <div class="mt-auto pt-3">
                    <x-button type="button" variant="danger" size="md" class="w-full" onclick="submitRevoke()">
                        <x-icon name="user-x" class="h-4 w-4" />
                        Revocar rol
                    </x-button>
                </div>
            </form>
        </section>
    </div>

    {{-- Consulta de Estado de Seguridad --}}
    <section aria-labelledby="security-lookup-heading" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)]">
        <div class="mb-5">
            <h2 id="security-lookup-heading" class="text-base font-bold text-[var(--color-foreground)]">Estado de seguridad de una cuenta</h2>
            <p class="text-xs text-[var(--color-muted-foreground)] mt-0.5">Consulta por ULID el estado de autenticación 2FA, bloqueos y sesiones activas.</p>
        </div>

        <form method="GET" action="{{ route('admin.security-status') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:flex-1">
                <label for="securityUserId" class="block text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)] mb-1.5">
                    ULID del usuario
                </label>
                <input
                    id="securityUserId"
                    type="text"
                    name="user_id"
                    value="{{ request('user_id') }}"
                    placeholder="01kvmbb1km2r6mmma5bd5x790n"
                    class="h-11 w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 font-mono text-sm text-[var(--color-foreground)] placeholder:text-[var(--color-muted-foreground)]/60 focus:border-[var(--color-primary)] focus:outline-none"
                >
            </div>

            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary" size="md">
                    <x-icon name="search" class="h-4 w-4" />
                    Consultar
                </x-button>

                @if (request()->filled('user_id'))
                    <a
                        href="{{ route('admin.users') }}"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-3.5 text-xs font-semibold text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors"
                    >
                        Limpiar
                    </a>
                @endif
            </div>
        </form>

        @if ($hasSecurityData)
            @php
                $twoFactor = !empty($data['two_factor_enabled']);
                $isBlocked = !empty($data['account_blocked']);
                $sessions  = (int) ($data['active_sessions'] ?? 0);
                $lastLogin = $data['last_login'] ?? null;
            @endphp

            <div class="mt-6 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/40 p-5">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-4">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">ID del usuario</dt>
                        <dd class="mt-1.5 truncate font-mono text-xs font-semibold text-[var(--color-foreground)]" title="{{ $data['user_id'] ?? request('user_id') }}">
                            {{ $data['user_id'] ?? request('user_id') ?? '—' }}
                        </dd>
                    </div>

                    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-4">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Autenticación 2FA</dt>
                        <dd class="mt-1.5">
                            <x-badge :variant="$twoFactor ? 'success' : 'warning'">
                                {{ $twoFactor ? 'Activa' : 'Inactiva' }}
                            </x-badge>
                        </dd>
                    </div>

                    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-4">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Estado de la cuenta</dt>
                        <dd class="mt-1.5">
                            <x-badge :variant="$isBlocked ? 'danger' : 'success'">
                                {{ $isBlocked ? 'Bloqueada' : 'Activa' }}
                            </x-badge>
                        </dd>
                    </div>

                    <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-4">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Sesiones activas</dt>
                        <dd class="mt-1 font-mono text-xl font-bold text-[var(--color-foreground)]">{{ $sessions }}</dd>
                    </div>
                </dl>

                <div class="mt-4 pt-3.5 border-t border-[var(--color-border)] flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">Último acceso registrado</span>
                    <span class="font-medium text-[var(--color-foreground)]">
                        {{ $lastLogin ? \Carbon\Carbon::parse($lastLogin)->translatedFormat('d M Y, H:i') : 'Sin registros' }}
                    </span>
                </div>
            </div>
        @elseif (request()->filled('user_id'))
            <div class="mt-6">
                <x-empty-state
                    title="Sin datos para ese usuario"
                    description="El servicio de identidad no devolvió información para el ULID consultado."
                />
            </div>
        @endif
    </section>
</x-page>

<script>
    function toggleAdminRoleDropdown(trigger) {
        const container = trigger.closest('[data-role-dropdown]');
        const menu = container.querySelector('[data-role-menu]');
        const chevron = container.querySelector('[data-role-chevron]');
        const isOpen = !menu.classList.contains('hidden');

        document.querySelectorAll('[data-role-menu]').forEach(m => m.classList.add('hidden'));
        document.querySelectorAll('[data-role-chevron]').forEach(c => c.classList.remove('rotate-180'));
        document.querySelectorAll('[data-role-dropdown] > button').forEach(b => b.setAttribute('aria-expanded', 'false'));

        if (!isOpen) {
            menu.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            if (chevron) chevron.classList.add('rotate-180');
        }
    }

    function selectAdminRole(optionBtn, inputId) {
        const container = optionBtn.closest('[data-role-dropdown]');
        const label = container.querySelector('[data-role-label]');
        const input = document.getElementById(inputId);

        input.value = optionBtn.dataset.value;
        label.textContent = optionBtn.dataset.label;
        label.classList.remove('text-[var(--color-muted-foreground)]');
        label.classList.add('text-[var(--color-foreground)]');

        container.querySelectorAll('.role-option').forEach(function (el) {
            el.classList.remove('bg-[var(--color-primary)]/10', 'font-semibold', 'text-[var(--color-primary)]');
            el.classList.add('text-[var(--color-foreground)]');
            el.setAttribute('aria-selected', 'false');
            const icon = el.querySelector('.role-check');
            if (icon) icon.classList.add('hidden');
        });

        optionBtn.classList.add('bg-[var(--color-primary)]/10', 'font-semibold', 'text-[var(--color-primary)]');
        optionBtn.classList.remove('text-[var(--color-foreground)]');
        optionBtn.setAttribute('aria-selected', 'true');
        const activeIcon = optionBtn.querySelector('.role-check');
        if (activeIcon) activeIcon.classList.remove('hidden');

        if (inputId === 'revoke-role-input') {
            document.getElementById('revokeRoleError').classList.add('hidden');
            document.getElementById('revokeRoleTrigger').classList.remove('border-[var(--color-destructive)]');
        }

        toggleAdminRoleDropdown(container.querySelector('button'));
    }

    function submitRevoke() {
        const userInput = document.getElementById('revokeUserId');
        const userId = userInput.value.trim();
        const role = document.getElementById('revoke-role-input').value;
        const form = document.getElementById('revokeForm');

        if (!userId) {
            userInput.reportValidity();
            return;
        }

        if (!role) {
            document.getElementById('revokeRoleError').classList.remove('hidden');
            document.getElementById('revokeRoleTrigger').classList.add('border-[var(--color-destructive)]');
            return;
        }

        form.action = '/admin/users/' + encodeURIComponent(userId) + '/roles/' + encodeURIComponent(role);
        form.submit();
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('[data-role-dropdown]')) {
            document.querySelectorAll('[data-role-menu]').forEach(m => m.classList.add('hidden'));
            document.querySelectorAll('[data-role-chevron]').forEach(c => c.classList.remove('rotate-180'));
            document.querySelectorAll('[data-role-dropdown] > button').forEach(b => b.setAttribute('aria-expanded', 'false'));
        }
    });
</script>
@endsection