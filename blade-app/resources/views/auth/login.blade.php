@extends('layouts.guest')

@section('title', 'Iniciar sesión | Synapse Campus')

@section('content')
<div class="w-full max-w-md">
    <x-card padding="p-8">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold tracking-tight font-[family-name:var(--font-serif)]">
                Synapse<span class="text-[var(--color-primary)]">Campus</span>
            </h1>
            <p class="text-sm text-[var(--color-muted-foreground)] mt-2">Ingresa con tu cuenta institucional.</p>
        </div>

        @if($errors->any())
            <x-alert type="error" class="mb-4 text-center">{{ $errors->first() }}</x-alert>
        @endif

        <form method="POST" action="/login" class="space-y-5">
            @csrf

            <x-field label="Correo institucional">
                <input type="email" name="email" value="{{ old('email') }}" autofocus required placeholder="usuario@ejemplo.com"
                    class="w-full h-11 px-4 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none text-sm">
            </x-field>

            <x-field label="Contraseña">
                <input type="password" name="password" required placeholder="••••••••"
                    class="w-full h-11 px-4 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none text-sm">
            </x-field>

            <x-button type="submit" variant="primary" size="md" class="w-full">Iniciar sesión</x-button>
        </form>

        <div class="flex items-center gap-4 my-6">
            <div class="flex-1 h-px bg-[var(--color-border)]"></div>
            <span class="text-[10px] uppercase font-bold text-[var(--color-muted-foreground)] tracking-wider">O continúa con</span>
            <div class="flex-1 h-px bg-[var(--color-border)]"></div>
        </div>

        <a href="{{ route('oauth.redirect', 'github') }}"
            class="flex items-center justify-center gap-3 w-full h-11 border border-[var(--color-border)] rounded-[var(--radius-md)] text-[var(--color-foreground)] text-sm font-bold hover:bg-[var(--color-muted)] transition-colors">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
            </svg>
            Continuar con GitHub
        </a>
    </x-card>
</div>
@endsection
