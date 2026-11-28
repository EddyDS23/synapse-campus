@extends('layouts.app')

@section('title', 'Activar 2FA | Synapse Campus')

@section('content')
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="setup-2fa-title">
    <div class="w-full max-w-md rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)] my-auto">
        <div class="flex items-center justify-between mb-2">
            <h1 id="setup-2fa-title" class="text-base sm:text-lg font-bold text-[var(--color-foreground)]">
                Activar Autenticación en Dos Pasos
            </h1>
            <a href="{{ route('security.index') }}" class="p-1.5 rounded-xl text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors" aria-label="Cancelar">
                <x-icon name="x" class="w-4 h-4" />
            </a>
        </div>

        <p class="text-xs text-[var(--color-muted-foreground)] leading-relaxed mb-4">
            Escanea este código QR con Google Authenticator, Authy o Microsoft Authenticator e ingresa el código de 6 dígitos para confirmar.
        </p>

        {{-- Contenedor QR con visor carmesí y tintado real solo en los módulos oscuros --}}
        <div class="flex flex-col items-center justify-center p-5 bg-[var(--color-muted)]/60 rounded-2xl border border-[var(--color-border)] mb-4">
            @if (!empty($qr))
            <div class="relative p-3.5 bg-white rounded-2xl shadow-sm border border-stone-200">
                {{-- Esquinas decorativas en color primario carmesí --}}
                <span class="absolute top-1.5 left-1.5 w-3.5 h-3.5 border-t-2 border-l-2 border-[var(--color-primary)] rounded-tl-md" aria-hidden="true"></span>
                <span class="absolute top-1.5 right-1.5 w-3.5 h-3.5 border-t-2 border-r-2 border-[var(--color-primary)] rounded-tr-md" aria-hidden="true"></span>
                <span class="absolute bottom-1.5 left-1.5 w-3.5 h-3.5 border-b-2 border-l-2 border-[var(--color-primary)] rounded-bl-md" aria-hidden="true"></span>
                <span class="absolute bottom-1.5 right-1.5 w-3.5 h-3.5 border-b-2 border-r-2 border-[var(--color-primary)] rounded-br-md" aria-hidden="true"></span>

                {{-- Aislamiento de fusión: blanco queda blanco, negro se tiñe de carmesí (#881337) --}}
                <div class="relative w-44 h-44 bg-white overflow-hidden rounded-lg isolate">
                    <img
                        src="data:image/svg+xml;base64,{{ $qr }}"
                        alt="Código QR 2FA"
                        class="w-full h-full object-contain block">
                    <div
                        class="absolute inset-0 pointer-events-none"
                        style="background: linear-gradient(135deg, #be123c 0%, #881337 55%, #4c0519 100%); mix-blend-mode: screen;"
                        aria-hidden="true"></div>
                </div>
            </div>

            <span class="text-[11px] font-medium text-[var(--color-muted-foreground)] mt-3">
                Compatible con Google Authenticator, Authy y Microsoft Authenticator
            </span>
            @else
            <div class="py-8 text-center text-xs text-[var(--color-muted-foreground)]">
                No se pudo generar el código QR. Intenta nuevamente.
            </div>
            @endif
        </div>

        <form method="POST" action="{{ route('security.2fa.verify') }}" class="space-y-4">
            @csrf
            <div>
                <label for="verify-code" class="block text-xs font-semibold text-[var(--color-foreground)] mb-1.5">
                    Código de verificación (6 dígitos)
                </label>
                <input
                    id="verify-code"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    required
                    autofocus
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)"
                    class="w-full h-12 px-4 rounded-xl bg-[var(--color-muted)]/60 border border-[var(--color-border)] text-center font-mono text-lg font-bold tracking-[0.35em] text-[var(--color-foreground)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
                @error('code')
                <p class="text-xs font-medium text-[var(--color-destructive)] mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <x-button href="{{ route('security.index') }}" variant="ghost" size="sm">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary" size="sm">
                    Verificar y Activar
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection