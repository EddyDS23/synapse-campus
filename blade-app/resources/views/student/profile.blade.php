@extends('layouts.app')

@section('title', 'Mi Perfil - Synapse Campus')

@section('content')
<x-page>
    <div class="flex flex-col md:flex-row items-center gap-8 mb-8">
        <x-avatar name="{{ session('email', 'U') }}" size="lg" class="w-24 h-24 md:w-32 md:h-32 text-2xl md:text-3xl rounded-[var(--radius-xl)]" />

        <div class="text-center md:text-left flex-1">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20 text-[var(--color-primary)] text-xs font-bold uppercase tracking-widest mb-3">
                <span class="h-2 w-2 rounded-full bg-[var(--color-success)]"></span>
                Expediente Digital Verificado
            </span>
            <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
                {{ explode('@', session('email', 'Usuario'))[0] }}
            </h1>
            <p class="text-[var(--color-muted-foreground)] text-lg mt-1">{{ $career ?? '' }}</p>
        </div>

        <div class="flex flex-col gap-2 w-full md:w-auto">
            <x-button variant="ghost" size="sm">Editar Información</x-button>
            <x-button variant="primary" size="sm">Exportar Kárdex</x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4 space-y-6">
            <x-card title="Datos de Control">
                <div class="space-y-5">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-[var(--color-muted-foreground)] mb-1">Número de Control</p>
                        <p class="text-xl font-mono font-bold text-[var(--color-foreground)] tracking-widest">{{ $number ?? '—' }}</p>
                    </div>
                    <div class="pt-4 border-t border-[var(--color-border)]">
                        <p class="text-xs uppercase tracking-wider text-[var(--color-muted-foreground)] mb-1">Semestre Actual</p>
                        <p class="text-3xl font-bold text-[var(--color-primary)] font-[family-name:var(--font-serif)]">{{ $semester ?? '—' }}° <span class="text-base font-normal text-[var(--color-muted-foreground)]">Ciclo</span></p>
                    </div>
                    <div class="pt-4 border-t border-[var(--color-border)]">
                        <p class="text-xs uppercase tracking-wider text-[var(--color-muted-foreground)] mb-2">Estatus Académico</p>
                        <x-badge variant="success">{{ strtoupper($status ?? '—') }}</x-badge>
                    </div>
                </div>
            </x-card>

            <x-card class="{{ ($has_debt ?? false) ? 'border-[var(--color-destructive)]/40' : 'border-[var(--color-success)]/40' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-[var(--color-muted-foreground)] mb-1">Estado Financiero</p>
                        <h4 class="text-lg font-bold {{ ($has_debt ?? false) ? 'text-[var(--color-destructive)]' : 'text-[var(--color-success)]' }}">
                            {{ ($has_debt ?? false) ? 'Pendiente de Pago' : 'Sin Adeudos' }}
                        </h4>
                    </div>
                    <div class="text-2xl {{ ($has_debt ?? false) ? 'text-[var(--color-destructive)]' : 'text-[var(--color-success)]' }}">
                        {{ ($has_debt ?? false) ? '⚠' : '✓' }}
                    </div>
                </div>
                <p class="text-xs text-[var(--color-muted-foreground)] mt-3 leading-relaxed">
                    {{ ($has_debt ?? false) ? 'Tienes trámites pendientes en tesorería. Favor de acudir a ventanilla.' : 'Tu cuenta está al corriente con todos los servicios institucionales.' }}
                </p>
            </x-card>
        </div>

        <div class="lg:col-span-8">
            <x-card title="Actividad en Plataforma" subtitle="Los datos de actividad se mostrarán cuando estén disponibles desde el servicio académico.">
                <p class="text-sm text-[var(--color-muted-foreground)]">No hay datos de actividad para mostrar por el momento.</p>
            </x-card>
        </div>
    </div>
</x-page>
@endsection
