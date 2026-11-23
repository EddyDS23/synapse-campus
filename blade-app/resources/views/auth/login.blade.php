@extends('layouts.guest')

@section('title', 'Iniciar sesión | Synapse Campus')

@section('content')
<div class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 rounded-[var(--radius-xl)] overflow-hidden shadow-[var(--shadow-lg)] border border-[var(--color-border)]">

    <!-- Panel izquierdo: Branding (oculto en móvil) -->
    <div class="hidden md:flex flex-col justify-between p-10 bg-gradient-to-br from-[var(--color-primary)] to-[var(--color-secondary)] text-[var(--color-on-primary)] relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full -ml-10 -mb-10 blur-2xl"></div>

        <div class="relative z-10">
            <h1 class="text-3xl font-bold tracking-tight font-[family-name:var(--font-serif)] mb-2">
                Synapse<span class="opacity-80">Campus</span>
            </h1>
            <p class="text-sm opacity-90 max-w-xs">
                Portal institucional del campus. Gestiona tu vida académica desde un solo lugar.
            </p>
        </div>

        <div class="relative z-10 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-[var(--radius-sm)] bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold">Acceso centralizado</p>
                    <p class="text-xs opacity-75">Un solo login para todos los servicios.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-[var(--radius-sm)] bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold">Datos académicos</p>
                    <p class="text-xs opacity-75">Perfil, horario, materias e inscripciones.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-[var(--radius-sm)] bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold">Soporte integrado</p>
                    <p class="text-xs opacity-75">Tickets y seguimiento en un clic.</p>
                </div>
            </div>
        </div>

        <p class="relative z-10 text-xs opacity-60">
            © {{ now()->year }} Synapse Campus
        </p>
    </div>

    <!-- Panel derecho: Formulario -->
    <div class="flex items-center justify-center p-8 md:p-12 bg-[var(--color-card)]">
        <div class="w-full max-w-sm">
            <h2 class="text-2xl font-bold tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)] mb-1">
                Iniciar sesión
            </h2>
            <p class="text-sm text-[var(--color-muted-foreground)] mb-6">
                Ingresa con tu cuenta institucional.
            </p>

            @if($errors->any())
                <x-alert type="error" class="mb-4">{{ $errors->first() }}</x-alert>
            @endif

            <form method="POST" action="/login" class="space-y-5">
                @csrf

                <x-field label="Correo institucional">
                    <input type="email" name="email" value="{{ old('email') }}" autofocus required placeholder="usuario@ejemplo.com" autocomplete="email"
                        class="w-full h-11 px-4 rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none text-sm">
                </x-field>

                <x-field label="Contraseña">
                    <input type="password" name="password" required placeholder="••••••••" autocomplete="current-password"
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
                class="flex items-center justify-center gap-3 w-full h-11 border border-[var(--color-border)] rounded-[var(--radius-md)] text-[var(--color-foreground)] text-sm font-bold hover:bg-[var(--color-muted)] transition-colors"
                aria-label="Continuar con GitHub">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
                </svg>
                Continuar con GitHub
            </a>
        </div>
    </div>
</div>
@endsection
