@extends('layouts.app')

@section('title', 'Seguridad | Synapse Campus')

@section('content')
<x-page>
    {{-- Cabecera --}}
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-[var(--color-card)] p-5 sm:p-6 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
                <span class="text-[11px] font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">AuthVault</span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold text-[var(--color-foreground)] tracking-tight">
                Seguridad y Auditoría de Cuenta
            </h1>
            <p class="text-xs text-[var(--color-muted-foreground)] mt-0.5">
                Gestiona tus sesiones activas, autenticación en dos pasos (2FA) y actividad reciente.
            </p>
        </div>
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[var(--color-muted)] border border-[var(--color-border)] text-xs font-semibold text-[var(--color-foreground)] self-start sm:self-center">
            <x-icon name="shield" class="w-4 h-4 text-[var(--color-primary)]" />
            Protección de Cuenta
        </span>
    </header>

    {{-- Grid superior: 2FA (Izquierda) + Sesiones con Scroll (Derecha) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Columna Izquierda: Autenticación en Dos Pasos (2FA) --}}
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20 text-[var(--color-primary)] flex items-center justify-center shrink-0">
                        <x-icon name="shield" class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-[var(--color-foreground)]">
                            Autenticación en Dos Pasos (2FA)
                        </h2>
                        <span class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[var(--color-muted)] border border-[var(--color-border)] text-[var(--color-muted-foreground)]">
                            Protección TOTP
                        </span>
                    </div>
                </div>

                <p class="text-xs sm:text-sm text-[var(--color-muted-foreground)] mt-4 leading-relaxed">
                    Añade una capa adicional de seguridad en AuthVault. Al iniciar sesión se solicitará un código temporal generado por tu aplicación autenticadora.
                </p>
            </div>

            <div class="mt-5 pt-4 border-t border-[var(--color-border)] flex flex-wrap items-center justify-between gap-3">
                <span class="text-xs text-[var(--color-muted-foreground)]">
                    Google / Microsoft Authenticator • Authy
                </span>

                <div class="flex items-center gap-2 shrink-0">
                    <button
                        type="button"
                        onclick="abrirModalDesactivar2FA()"
                        class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-[var(--color-destructive)]/30 bg-[color-mix(in_srgb,var(--color-destructive)_8%,transparent)] px-3 text-xs font-semibold text-[var(--color-destructive)] hover:bg-[color-mix(in_srgb,var(--color-destructive)_15%,transparent)] transition-colors cursor-pointer"
                    >
                        <x-icon name="unlock" class="w-3.5 h-3.5" />
                        Desactivar
                    </button>

                    <form method="POST" action="{{ route('security.2fa.enable') }}">
                        @csrf
                        <x-button type="submit" variant="primary" size="sm">
                            <x-icon name="key" class="w-3.5 h-3.5" />
                            Activar 2FA
                        </x-button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Columna Derecha: Dispositivos y Sesiones con Scroll --}}
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 rounded-2xl bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20 text-[var(--color-primary)] flex items-center justify-center shrink-0">
                        <x-icon name="monitor" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-[var(--color-foreground)]">
                                Dispositivos y Sesiones
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[var(--color-muted)] border border-[var(--color-border)] text-[var(--color-muted-foreground)]">
                                {{ count($sessions) }} {{ count($sessions) === 1 ? 'activa' : 'activas' }}
                            </span>
                        </div>
                        <p class="text-xs text-[var(--color-muted-foreground)]">
                            Gestiona dónde tienes tu cuenta abierta
                        </p>
                    </div>
                </div>

                {{-- Lista con scroll igual a la preview --}}
                <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                    @forelse ($sessions as $session)
                        <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/45">
                            <div class="flex items-center gap-3 min-w-0">
                                <x-icon name="monitor" class="w-5 h-5 text-[var(--color-muted-foreground)] shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-[var(--color-foreground)] truncate">
                                        {{ $session['device'] ?? 'Dispositivo desconocido' }}
                                    </p>
                                    <p class="text-[11px] font-mono text-[var(--color-muted-foreground)] mt-0.5 truncate">
                                        IP: {{ $session['ip_address'] }} • Expira {{ \Carbon\Carbon::parse($session['expires_at'])->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('security.session.delete', $session['id']) }}" class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-xs font-semibold text-[var(--color-destructive)] hover:bg-[color-mix(in_srgb,var(--color-destructive)_10%,transparent)] px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer"
                                >
                                    Cerrar
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="py-10 text-center rounded-xl border border-dashed border-[var(--color-border)] bg-[var(--color-muted)]/30">
                            <p class="text-xs text-[var(--color-muted-foreground)]">No hay sesiones activas registradas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Registro de Actividad con Scroll y Encabezado Fijo --}}
    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] overflow-hidden">
        <div class="p-6 border-b border-[var(--color-border)] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-[var(--color-foreground)]">
                    Registro de Actividad de Seguridad
                </h2>
                <p class="text-xs text-[var(--color-muted-foreground)] mt-0.5">
                    Historial auditado de accesos y movimientos recientes en tu cuenta.
                </p>
            </div>
            <span class="text-xs font-semibold text-[var(--color-muted-foreground)] bg-[var(--color-muted)] border border-[var(--color-border)] px-3 py-1.5 rounded-xl self-start">
                {{ count($auditLogs) }} {{ count($auditLogs) === 1 ? 'registro' : 'registros' }}
            </span>
        </div>

        <div class="overflow-x-auto max-h-80 overflow-y-auto">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10 bg-[var(--color-muted)] border-b border-[var(--color-border)]">
                    <tr class="text-[11px] font-bold text-[var(--color-muted-foreground)] uppercase tracking-wider">
                        <th class="py-3.5 px-6">Dirección IP</th>
                        <th class="py-3.5 px-6">Acción Realizada</th>
                        <th class="py-3.5 px-6">Servicio</th>
                        <th class="py-3.5 px-6">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border)] text-xs">
                    @forelse ($auditLogs as $log)
                        <tr class="hover:bg-[var(--color-muted)]/35 transition-colors">
                            <td class="py-3.5 px-6 font-mono font-semibold text-[var(--color-foreground)] whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-[var(--color-muted)] border border-[var(--color-border)]">
                                    {{ $log['ip_address'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 font-medium text-[var(--color-foreground)]">
                                {{ $log['action'] }}
                            </td>
                            <td class="py-3.5 px-6">
                                @if (!empty($log['service']))
                                    <x-badge variant="neutral">{{ $log['service'] }}</x-badge>
                                @else
                                    <span class="text-[var(--color-muted-foreground)]">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-[var(--color-muted-foreground)] whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-xs text-[var(--color-muted-foreground)]">
                                No hay actividad registrada por el momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-page>

{{-- Modal: Desactivar Autenticación 2FA --}}
<div
    id="modal-disable-2fa"
    class="fixed inset-0 z-50 {{ $errors->has('code') ? 'flex' : 'hidden' }} items-center justify-center bg-black/60 backdrop-blur-xs p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-disable-title"
>
    <div class="w-full max-w-md rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)]">
        <div class="flex items-center justify-between mb-3">
            <h3 id="modal-disable-title" class="text-base font-bold text-[var(--color-foreground)]">
                Desactivar Autenticación 2FA
            </h3>
            <button type="button" onclick="cerrarModalDesactivar2FA()" class="p-1.5 rounded-xl text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors cursor-pointer" aria-label="Cerrar">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>

        <div class="p-3.5 rounded-xl bg-[color-mix(in_srgb,var(--color-warning)_10%,transparent)] border border-[color-mix(in_srgb,var(--color-warning)_25%,transparent)] text-[var(--color-warning)] text-xs mb-4 flex items-start gap-2.5">
            <x-icon name="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5" />
            <p class="leading-relaxed">Al desactivar 2FA, tu cuenta institucional dejará de solicitar el código temporal de tu app autenticadora.</p>
        </div>

        <form method="POST" action="{{ route('security.2fa.disable') }}" class="space-y-4">
            @csrf
            <div>
                <label for="disable-code" class="block text-xs font-semibold text-[var(--color-foreground)] mb-1.5">
                    Código actual de tu app autenticadora
                </label>
                <input
                    id="disable-code"
                    type="text"
                    name="code"
                    maxlength="10"
                    placeholder="000000"
                    required
                    class="w-full h-11 px-4 rounded-xl bg-[var(--color-muted)]/60 border border-[var(--color-border)] text-center font-mono text-base tracking-widest text-[var(--color-foreground)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]"
                >
                @error('code')
                    <p class="text-xs font-medium text-[var(--color-destructive)] mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-button type="button" variant="ghost" size="sm" onclick="cerrarModalDesactivar2FA()">
                    Mantener Activado
                </x-button>
                <x-button type="submit" variant="danger" size="sm">
                    Sí, Desactivar 2FA
                </x-button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalDesactivar2FA() {
        const modal = document.getElementById('modal-disable-2fa');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        const input = document.getElementById('disable-code');
        if (input) input.focus();
    }

    function cerrarModalDesactivar2FA() {
        const modal = document.getElementById('modal-disable-2fa');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection