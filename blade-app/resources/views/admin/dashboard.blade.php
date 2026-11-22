@extends('layouts.app')

@section('title', 'Panel de administración - Synapse Campus')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold font-[family-name:var(--font-serif)]">Panel de administración</h1>
            <p class="text-sm text-[var(--color-muted-foreground)]">Accesos rápidos a las herramientas de gestión.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.users') }}" class="block">
            <x-card title="Usuarios y roles" subtitle="Asignar y revocar roles institucionales" hover>
                <p class="text-sm text-[var(--color-muted-foreground)]">Gestión de identidades y permisos.</p>
            </x-card>
        </a>
        <a href="{{ route('admin.audit') }}" class="block">
            <x-card title="Auditoría" subtitle="Registro de eventos del ecosistema" hover>
                <p class="text-sm text-[var(--color-muted-foreground)]">Consulta los logs centralizados.</p>
            </x-card>
        </a>
        <a href="{{ route('admin.security-status') }}" class="block">
            <x-card title="Estado de seguridad" subtitle="Diagnóstico de cuentas" hover>
                <p class="text-sm text-[var(--color-muted-foreground)]">Revisa el estado de seguridad de un usuario.</p>
            </x-card>
        </a>
    </div>
</div>
@endsection
