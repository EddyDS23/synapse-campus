@extends('layouts.guest')

@section('title', ($title ?? 'Servicio no disponible') . ' | Synapse Campus')

@section('content')
@php
    $statusCode = $code ?? 503;
    $pageTitle = !empty($title) ? $title : 'Servicio no disponible';
    $errorMessage = !empty($message)
        ? $message
        : 'Uno de los servicios internos del campus no responde en este momento. La interrupción suele ser temporal; inténtalo de nuevo en unos segundos.';

    $dashboardUrl = \Illuminate\Support\Facades\Route::has('dashboard')
        ? route('dashboard')
        : url('/dashboard');

    $supportUrl = \Illuminate\Support\Facades\Route::has('support.create')
        ? route('support.create')
        : null;

    $incidentTime = now()->format('d/m/Y H:i:s');
@endphp

<div class="min-h-[calc(100dvh-2rem)] w-full flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-xl rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-md)] overflow-hidden">
        {{-- Cabecera Carbon (30% secundario) --}}
        <div class="flex items-center justify-between gap-3 bg-[var(--color-chrome-bg)] border-b border-[var(--color-chrome-border)] px-6 py-4">
            <div class="flex items-center gap-2.5">
                <span class="w-4 h-1 rounded-full bg-[var(--color-primary)]"></span>
                <span class="text-xs font-bold uppercase tracking-widest text-stone-100">
                    Synapse Campus
                </span>
            </div>

            <span class="inline-flex items-center gap-1.5 rounded-lg border border-rose-400/30 bg-rose-500/15 px-2.5 py-1 font-mono text-xs font-bold text-rose-200">
                <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                HTTP {{ $statusCode }}
            </span>
        </div>

        {{-- Cuerpo principal Stone (60% dominante) --}}
        <div class="p-6 sm:p-8 space-y-6">
            {{-- Encabezado del error --}}
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[var(--color-primary)]/20 bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="3" width="20" height="7" rx="2" />
                        <rect x="2" y="14" width="20" height="7" rx="2" />
                        <line x1="6" y1="6.5" x2="6.01" y2="6.5" />
                        <line x1="6" y1="17.5" x2="6.01" y2="17.5" />
                        <path d="M14 17.5h4" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--color-primary)]">
                        Interrupción de servicio
                    </p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-[var(--color-foreground)] leading-tight">
                        {{ $pageTitle }}
                    </h1>
                </div>
            </div>

            {{-- Detalle técnico y marca de tiempo --}}
            <div class="rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/55 overflow-hidden">
                <div class="p-4 text-sm leading-relaxed text-[var(--color-foreground)]" id="error-detail-text">
                    {{ $errorMessage }}
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[var(--color-border)] bg-[var(--color-muted)]/80 px-4 py-2.5 text-xs text-[var(--color-muted-foreground)]">
                    <span class="font-mono">Registro: {{ $incidentTime }}</span>

                    <button
                        type="button"
                        id="copy-error-btn"
                        onclick="copyErrorDetail()"
                        class="inline-flex items-center gap-1.5 font-semibold text-[var(--color-foreground)] hover:text-[var(--color-primary)] transition-colors cursor-pointer"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                        </svg>
                        <span id="copy-error-label">Copiar detalle</span>
                    </button>
                </div>
            </div>

            {{-- Orientación rápida --}}
            <ul class="space-y-2 text-xs sm:text-sm text-[var(--color-muted-foreground)]">
                <li class="flex items-center gap-2.5">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[var(--color-primary)]"></span>
                    <span>Espera unos segundos y pulsa <strong class="font-semibold text-[var(--color-foreground)]">Reintentar</strong> para verificar si el módulo ya respondió.</span>
                </li>
                <li class="flex items-center gap-2.5">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[var(--color-muted-foreground)]"></span>
                    <span>Puedes seguir utilizando el resto de módulos desde el panel principal.</span>
                </li>
            </ul>

            {{-- Barra de acciones (10% Crimson) --}}
            <div class="pt-5 border-t border-[var(--color-border)] flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <a
                    href="{{ $dashboardUrl }}"
                    class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-[var(--color-primary)] px-4 text-sm font-semibold text-white hover:opacity-90 transition-opacity"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5" />
                        <path d="M12 19l-7-7 7-7" />
                    </svg>
                    Volver al dashboard
                </a>

                <button
                    type="button"
                    id="retry-service-btn"
                    onclick="retryCurrentPage(this)"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/60 px-4 text-sm font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors cursor-pointer"
                >
                    <svg id="retry-service-icon" class="h-4 w-4 text-[var(--color-muted-foreground)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                        <path d="M3 3v5h5" />
                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                        <path d="M16 16h5v5" />
                    </svg>
                    <span id="retry-service-label">Reintentar</span>
                </button>

                @if ($supportUrl)
                    <a
                        href="{{ $supportUrl }}"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-3.5 text-xs font-semibold text-[var(--color-muted-foreground)] hover:bg-[var(--color-muted)] hover:text-[var(--color-foreground)] transition-colors"
                    >
                        Reportar
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function retryCurrentPage(btn) {
        const icon = document.getElementById('retry-service-icon');
        const label = document.getElementById('retry-service-label');

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-wait');
        if (icon) icon.classList.add('animate-spin', 'text-[var(--color-primary)]');
        if (label) label.textContent = 'Verificando...';

        window.location.reload();
    }

    function copyErrorDetail() {
        const detail = document.getElementById('error-detail-text')?.innerText?.trim() || '';
        const payload = '[HTTP {{ $statusCode }} · {{ $incidentTime }}] ' + detail;
        const label = document.getElementById('copy-error-label');

        if (navigator.clipboard && payload) {
            navigator.clipboard.writeText(payload).then(function () {
                if (label) {
                    label.textContent = 'Copiado';
                    setTimeout(function () {
                        label.textContent = 'Copiar detalle';
                    }, 2000);
                }
            });
        }
    }
</script>
@endsection