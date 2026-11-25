@extends('layouts.app')

@section('title', 'Nuevo Ticket | Synapse')

@section('content')
<x-page>
    <header class="mb-8">
        <a href="{{ route('support.my-tickets') }}" class="mb-4 inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--color-muted-foreground)] transition hover:text-[var(--color-foreground)]">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" />
            Volver a mis tickets
        </a>
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--color-muted-foreground)]">Soporte</p>
        <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
            Reportar una incidencia
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
            Describe el problema con el mayor detalle posible para que el equipo de soporte pueda atenderlo.
        </p>
    </header>

    @if ($errors->any())
        <x-alert type="error" class="mb-6">
            <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
            <div>
                <p class="font-semibold">Revisa los siguientes campos:</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-4 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </x-alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('support.create.ticket') }}" class="space-y-5 lg:col-span-2">
            @csrf

            <x-card>
                <div class="space-y-5">
                    <x-field label="Título del requerimiento" :error="$errors->first('title')">
                        <input
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            maxlength="255"
                            required
                            placeholder="Ej. Error al abrir el catálogo de la biblioteca"
                            class="h-11 w-full rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-4 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                        >
                    </x-field>

                    <x-field label="Descripción detallada" :error="$errors->first('description')">
                        <textarea
                            name="description"
                            rows="7"
                            required
                            placeholder="Explica qué ocurrió, los pasos para reproducirlo y cualquier mensaje de error que hayas visto."
                            class="w-full resize-y rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-4 py-3 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                        >{{ old('description') }}</textarea>
                    </x-field>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-field label="Prioridad del impacto" :error="$errors->first('priority')">
                            <select
                                name="priority"
                                class="h-11 w-full rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                            >
                                <option value="low" @selected(old('priority') === 'low')>Baja · Consulta general</option>
                                <option value="medium" @selected(old('priority', 'medium') === 'medium')>Media · Problema menor</option>
                                <option value="high" @selected(old('priority') === 'high')>Alta · Fallo de servicio</option>
                                <option value="urgent" @selected(old('priority') === 'urgent')>Urgente · Bloqueo total</option>
                            </select>
                        </x-field>

                        <x-field label="ID de categoría" hint="Código de categoría que corresponde a tu reporte." :error="$errors->first('category_id')">
                            <input
                                type="text"
                                name="category_id"
                                value="{{ old('category_id') }}"
                                required
                                placeholder="CAT-0000"
                                class="h-11 w-full rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-4 font-mono text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                            >
                        </x-field>
                    </div>
                </div>
            </x-card>

            <div class="flex flex-wrap items-center gap-3">
                <x-button type="submit" variant="primary" size="md">
                    <x-icon name="send" class="h-4 w-4" />
                    Crear ticket
                </x-button>
                <x-button :href="route('support.my-tickets')" variant="ghost" size="md">Cancelar</x-button>
            </div>
        </form>

        <aside class="space-y-4">
            <x-card title="Qué incluir">
                <ul class="space-y-3 text-sm text-[var(--color-muted-foreground)]">
                    <li class="flex gap-3">
                        <span class="mt-0.5 text-[var(--color-primary)]"><x-icon name="check" class="h-4 w-4" /></span>
                        <span>Un título específico: evita títulos genéricos como “Ayuda” o “Error”.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 text-[var(--color-primary)]"><x-icon name="check" class="h-4 w-4" /></span>
                        <span>Los pasos exactos para reproducir el problema.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 text-[var(--color-primary)]"><x-icon name="check" class="h-4 w-4" /></span>
                        <span>El mensaje o código de error, si aparece en pantalla.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-0.5 text-[var(--color-primary)]"><x-icon name="check" class="h-4 w-4" /></span>
                        <span>Si el problema afecta a más usuarios o solo a tu cuenta.</span>
                    </li>
                </ul>
            </x-card>

            <x-card class="border-[var(--color-accent)]/30">
                <div class="flex gap-3">
                    <span class="mt-0.5 shrink-0 text-[var(--color-accent)]"><x-icon name="info" class="h-5 w-5" /></span>
                    <div>
                        <p class="text-sm font-semibold text-[var(--color-foreground)]">Sobre la prioridad</p>
                        <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                            La prioridad orienta al equipo de soporte, pero no garantiza un tiempo de respuesta específico.
                        </p>
                    </div>
                </div>
            </x-card>
        </aside>
    </div>
</x-page>
@endsection
