@extends('layouts.guest')

@section('title', 'Verificación 2FA | Synapse Campus')

@section('content')
<div class="min-h-screen flex flex-col lg:flex-row">

    <!-- Panel Institucional Izquierdo -->
    <aside class="lg:w-5/12 xl:w-1/2 bg-[var(--color-chrome-bg)] border-b lg:border-b-0 lg:border-r border-[var(--color-chrome-border)] p-8 sm:p-12 lg:p-16 flex flex-col justify-between text-[var(--color-chrome-muted)] relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-[var(--color-primary)]"></div>

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-[var(--color-primary)] text-[var(--color-on-primary)] flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <div>
                    <span class="font-bold text-[var(--color-chrome-fg)] text-xl tracking-tight leading-tight block">Synapse Campus</span>
                    <span class="block text-[11px] font-semibold uppercase tracking-widest text-[var(--color-chrome-muted)]">Ecosistema Académico</span>
                </div>
            </div>

            <button type="button" onclick="toggleTheme()" class="lg:hidden p-2.5 rounded-xl bg-[var(--color-chrome-elevated)] text-[var(--color-chrome-muted)] hover:text-[var(--color-chrome-fg)] transition-colors cursor-pointer" title="Cambiar tema">
                <svg id="theme-icon-mobile" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>

        <div class="my-10 lg:my-0 space-y-6 max-w-md">
            <div class="w-10 h-1 rounded-full bg-[var(--color-primary)]"></div>
            <h1 class="text-3xl sm:text-4xl font-bold text-[var(--color-chrome-fg)] tracking-tight leading-tight">
                Protección de Identidad Institucional
            </h1>
            <p class="text-sm sm:text-base text-[var(--color-chrome-muted)] leading-relaxed">
                Tu cuenta cuenta con autenticación multifactor activa para resguardar tu información académica y sesiones activas.
            </p>
        </div>

        <div class="text-xs text-[var(--color-chrome-muted)]/70 pt-4 border-t border-[var(--color-chrome-border)]">
            <span>&copy; {{ now()->year }} Synapse Campus • Todos los derechos reservados</span>
        </div>
    </aside>

    <!-- Área Derecha 2FA -->
    <main class="flex-1 flex flex-col justify-between p-6 sm:p-12 lg:p-16 relative bg-[var(--color-background)]">
        <div class="hidden lg:flex justify-end">
            <button type="button" onclick="toggleTheme()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[var(--color-card)] border border-[var(--color-border)] text-[var(--color-muted-foreground)] hover:text-[var(--color-foreground)] text-xs font-semibold shadow-[var(--shadow-sm)] transition-all cursor-pointer">
                <svg id="theme-icon-desktop" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <span id="theme-label">Modo Oscuro</span>
            </button>
        </div>

        <div class="w-full max-w-md mx-auto my-auto py-6">
            <div class="bg-[var(--color-card)] rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)] p-7 sm:p-9 space-y-6">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-[var(--color-primary)]/10 text-[var(--color-primary)] flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-[var(--color-foreground)] tracking-tight">Verificación en Dos Pasos</h2>
                        <p class="text-xs text-[var(--color-muted-foreground)] mt-0.5">Ingresa el código de 6 dígitos de tu app autenticadora.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('login.2fa') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="otp-code" class="block text-xs font-semibold text-[var(--color-muted-foreground)] uppercase tracking-wider mb-2">
                            Código de Seguridad (TOTP)
                        </label>
                        <input type="text" id="otp-code" name="code" autofocus maxlength="10" placeholder="000000" required
                            class="w-full px-4 py-3 rounded-xl bg-[var(--color-background)] border border-[var(--color-border)] text-[var(--color-foreground)] text-center font-mono text-2xl tracking-[0.4em] focus:bg-[var(--color-card)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]">
                        <p class="text-[11px] text-[var(--color-muted-foreground)] mt-2 text-center">
                            También puedes ingresar uno de tus códigos de recuperación.
                        </p>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)] text-[var(--color-on-primary)] font-semibold text-sm shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <span>Verificar e Ingresar</span>
                    </button>

                    <a href="{{ route('login') }}" class="w-full py-2 text-xs font-semibold text-[var(--color-muted-foreground)] hover:text-[var(--color-foreground)] transition-colors flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        <span>Volver al inicio de sesión</span>
                    </a>
                </form>
            </div>
        </div>

        <div class="text-center text-xs text-[var(--color-muted-foreground)]">
            ¿Problemas para acceder a tu cuenta?
            <span class="font-semibold text-[var(--color-primary)]">Contacta a Soporte Técnico</span>
        </div>
    </main>
</div>
@endsection
