@extends('layouts.guest')

@section('title', 'Verificación 2FA | Synapse Campus')

@section('content')
<div class="w-full max-w-md">
    <x-card padding="p-8">
        <div class="text-center mb-6">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20 text-[var(--color-primary)] flex items-center justify-center">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight font-[family-name:var(--font-serif)]">
                Verificación en <span class="text-[var(--color-primary)]">dos pasos</span>
            </h1>
            <p class="text-sm text-[var(--color-muted-foreground)] mt-2">Ingresa el código de tu aplicación autenticadora.</p>
        </div>

        @if($errors->any())
            <x-alert type="error" class="mb-4 text-center">{{ $errors->first() }}</x-alert>
        @endif

        <form method="POST" action="{{ route('login.2fa') }}" class="space-y-6">
            @csrf

            <x-field label="Código TOTP">
                <input type="text" name="code" autofocus maxlength="10" placeholder="000000"
                    class="w-full h-12 px-4 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none text-center text-2xl font-mono tracking-[0.25em]">
            </x-field>

            <x-button type="submit" variant="primary" size="md" class="w-full">Verificar Código</x-button>

            <div class="text-center">
                <a href="{{ route('login') }}" class="text-xs font-bold text-[var(--color-muted-foreground)] hover:text-[var(--color-primary)] transition-colors">← Volver al login</a>
            </div>
        </form>

        <div class="mt-6 p-4 rounded-[var(--radius-md)] bg-[var(--color-muted)] border border-[var(--color-border)] text-center">
            <p class="text-xs text-[var(--color-muted-foreground)]">
                ¿No tienes acceso a tu app? Usa uno de tus <strong class="text-[var(--color-primary)] font-bold">códigos de recuperación</strong> en su lugar.
            </p>
        </div>
    </x-card>
</div>
@endsection
