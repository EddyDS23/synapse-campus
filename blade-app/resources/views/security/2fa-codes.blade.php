@extends('layouts.app')

@section('title', 'Códigos de Recuperación 2FA | Synapse Campus')

@section('content')
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="codes-2fa-title">
    <div class="w-full max-w-md rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)] my-auto">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[color-mix(in_srgb,var(--color-success)_15%,transparent)] text-[var(--color-success)] flex items-center justify-center">
                    <x-icon name="check" class="w-4 h-4" />
                </span>
                <h1 id="codes-2fa-title" class="text-base sm:text-lg font-bold text-[var(--color-foreground)]">
                    Códigos de Recuperación 2FA
                </h1>
            </div>
            <a href="{{ route('security.index') }}" class="p-1.5 rounded-xl text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors" aria-label="Cerrar">
                <x-icon name="x" class="w-4 h-4" />
            </a>
        </div>

        <p class="text-xs text-[var(--color-muted-foreground)] leading-relaxed mb-4">
            Guarda estos códigos en un lugar seguro. Cada código puede usarse una sola vez si pierdes acceso a tu dispositivo autenticador.
        </p>

        <div class="grid grid-cols-2 gap-2.5 p-4 rounded-xl bg-[var(--color-muted)]/60 border border-[var(--color-border)] font-mono text-xs text-center text-[var(--color-foreground)] mb-4">
            @foreach ($codes as $code)
                <div class="py-2 px-3 rounded-lg bg-[var(--color-card)] border border-[var(--color-border)] font-bold tracking-wider select-all">
                    {{ $code }}
                </div>
            @endforeach
        </div>

        <div class="p-3.5 rounded-xl bg-[color-mix(in_srgb,var(--color-warning)_10%,transparent)] border border-[color-mix(in_srgb,var(--color-warning)_25%,transparent)] text-[var(--color-warning)] text-xs mb-5 flex items-start gap-2.5">
            <x-icon name="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5" />
            <p class="leading-relaxed">No podrás volver a ver estos códigos después de cerrar esta ventana.</p>
        </div>

        <div class="flex justify-end">
            <x-button href="{{ route('security.index') }}" variant="primary" size="sm" class="w-full sm:w-auto">
                Entendido, ya los guardé
            </x-button>
        </div>
    </div>
</div>
@endsection