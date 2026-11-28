@extends('layouts.guest')

@section('title', 'Iniciar Sesión | Synapse Campus')

@section('content')
<div class="min-h-screen flex flex-col lg:flex-row">

    <!-- ==========================================
         30% SECUNDARIO: Panel Institucional Izquierdo (Carbón Profundo)
    =========================================== -->
    <aside class="lg:w-5/12 xl:w-1/2 bg-[var(--color-chrome-bg)] border-b lg:border-b-0 lg:border-r border-[var(--color-chrome-border)] p-8 sm:p-12 lg:p-16 flex flex-col justify-between text-[var(--color-chrome-muted)] relative overflow-hidden">
        <!-- Franja superior de acento 10% -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-[var(--color-primary)]"></div>

        <!-- Cabecera / Marca -->
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

            <!-- Botón Tema Móvil -->
            <button type="button" onclick="toggleTheme()" class="lg:hidden p-2.5 rounded-xl bg-[var(--color-chrome-elevated)] text-[var(--color-chrome-muted)] hover:text-[var(--color-chrome-fg)] transition-colors cursor-pointer" title="Cambiar tema" aria-label="Cambiar tema">
                <svg id="theme-icon-mobile" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>

        <!-- Mensaje Central Institucional -->
        <div class="my-10 lg:my-0 space-y-8 max-w-md">
            <div class="space-y-4">
                <div class="w-10 h-1 rounded-full bg-[var(--color-primary)]"></div>
                <h1 class="text-3xl sm:text-4xl font-bold text-[var(--color-chrome-fg)] tracking-tight leading-tight">
                    Plataforma Central de Gestión Académica
                </h1>
                <p class="text-sm sm:text-base text-[var(--color-chrome-muted)] leading-relaxed">
                    Accede de forma segura al control escolar, consulta tu historial académico, administra recursos bibliotecarios y gestiona los servicios digitales de la institución.
                </p>
            </div>

            <!-- Pilares del Sistema -->
            <div class="pt-6 space-y-4 border-t border-[var(--color-chrome-border)]">
                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-[var(--color-chrome-elevated)] text-[#FB7185] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-chrome-fg)]">Seguridad Institucional</p>
                        <p class="text-xs text-[var(--color-chrome-muted)] mt-0.5">Autenticación protegida por AuthVault y soporte 2FA</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5">
                    <div class="w-9 h-9 rounded-xl bg-[var(--color-chrome-elevated)] text-[#FB7185] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-chrome-fg)]">Servicios Integrados</p>
                        <p class="text-xs text-[var(--color-chrome-muted)] mt-0.5">Gestión académica, biblioteca y mesa de soporte</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pie del Panel Izquierdo -->
        <div class="text-xs text-[var(--color-chrome-muted)]/70 pt-4 border-t border-[var(--color-chrome-border)]">
            <span>&copy; {{ now()->year }} Synapse Campus • Todos los derechos reservados</span>
        </div>
    </aside>

    <!-- ==========================================
         60% NEUTRO: Área de Formulario de Acceso
    =========================================== -->
    <main class="flex-1 flex flex-col justify-between p-6 sm:p-12 lg:p-16 relative bg-[var(--color-background)]">
        <!-- Barra superior derecha (Selector Light/Dark en Desktop) -->
        <div class="hidden lg:flex justify-end">
            <button type="button" onclick="toggleTheme()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[var(--color-card)] border border-[var(--color-border)] text-[var(--color-muted-foreground)] hover:text-[var(--color-foreground)] text-xs font-semibold shadow-[var(--shadow-sm)] transition-all cursor-pointer">
                <svg id="theme-icon-desktop" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <span id="theme-label">Modo Oscuro</span>
            </button>
        </div>

        <!-- Contenedor Centrado de Tarjeta -->
        <div class="w-full max-w-md mx-auto my-auto py-6">
            <div class="bg-[var(--color-card)] rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)] p-7 sm:p-9 space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-[var(--color-foreground)] tracking-tight">Iniciar Sesión</h2>
                    <p class="text-sm text-[var(--color-muted-foreground)] mt-1">Ingresa tus credenciales institucionales para continuar.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Correo Institucional -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-[var(--color-muted-foreground)] uppercase tracking-wider mb-1.5">
                            Correo Institucional
                        </label>
                        <div class="relative">
                            <svg class="w-4 h-4 text-[var(--color-muted-foreground)] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" autofocus required placeholder="usuario@institucion.edu.mx" autocomplete="email"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[var(--color-background)] border border-[var(--color-border)] text-[var(--color-foreground)] text-sm focus:bg-[var(--color-card)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="passwordInput" class="block text-xs font-semibold text-[var(--color-muted-foreground)] uppercase tracking-wider mb-1.5">
                            Contraseña
                        </label>
                        <div class="relative">
                            <svg class="w-4 h-4 text-[var(--color-muted-foreground)] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input type="password" id="passwordInput" name="password" required placeholder="••••••••••••" autocomplete="current-password"
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-[var(--color-background)] border border-[var(--color-border)] text-[var(--color-foreground)] text-sm focus:bg-[var(--color-card)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-transparent transition-all">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--color-muted-foreground)] hover:text-[var(--color-foreground)] p-0.5 cursor-pointer" title="Mostrar/Ocultar contraseña" aria-label="Mostrar contraseña">
                                <svg id="eyeIcon" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- 10% ACENTO: Botón Principal de Envío -->
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)] text-[var(--color-on-primary)] font-semibold text-sm shadow-sm transition-all flex items-center justify-center gap-2 mt-2 cursor-pointer">
                        <span>Entrar al Portal</span>
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>
                </form>

                <!-- Separador OAuth -->
                <div class="relative py-1">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-[var(--color-border)]"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-3 bg-[var(--color-card)] text-[var(--color-muted-foreground)] font-medium">O continúa con</span>
                    </div>
                </div>

                <!-- Botón GitHub OAuth -->
                <a href="{{ route('oauth.redirect', 'github') }}"
                    class="w-full py-2.5 px-4 rounded-xl bg-[var(--color-background)] hover:bg-[var(--color-muted)] border border-[var(--color-border)] text-[var(--color-foreground)] font-semibold text-xs transition-all flex items-center justify-center gap-2.5"
                    aria-label="Continuar con GitHub">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"></path>
                    </svg>
                    <span>Continuar con GitHub</span>
                </a>
            </div>
        </div>

        <!-- Pie de página de ayuda -->
        <div class="text-center text-xs text-[var(--color-muted-foreground)]">
            ¿Problemas para acceder a tu cuenta?
            <span class="font-semibold text-[var(--color-primary)]">Contacta a Soporte Técnico</span>
        </div>
    </main>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    }
</script>
@endsection