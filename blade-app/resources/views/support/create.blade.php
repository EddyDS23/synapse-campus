@extends('layouts.app')

@section('title', 'Nuevo Ticket | Synapse Campus')

@section('content')
@php
    $selectedPriority = old('priority', 'medium');
    $priorityOptions = [
        'low'    => 'Baja · Consulta general',
        'medium' => 'Media · Problema menor',
        'high'   => 'Alta · Fallo de servicio',
        'urgent' => 'Urgente · Bloqueo total',
    ];
@endphp

<x-page>
    <x-breadcrumb :items="[
        ['label' => 'Soporte', 'url' => route('support.my-tickets')],
        ['label' => 'Mis tickets', 'url' => route('support.my-tickets')],
        ['label' => 'Reportar incidencia'],
    ]" />

    <header class="bg-[var(--color-card)] p-6 sm:p-7 rounded-2xl border border-[var(--color-border)] shadow-[var(--shadow-sm)]">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Mesa de Ayuda</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
            Reportar una incidencia
        </h1>
        <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1 max-w-2xl">
            Describe el problema con el mayor detalle posible para que el equipo de soporte pueda atenderlo.
        </p>
    </header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 items-start">
        <form method="POST" action="{{ route('support.create.ticket') }}" class="space-y-5 lg:col-span-2">
            @csrf

            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-sm)] space-y-5">
                <x-field label="Título del requerimiento" :error="$errors->first('title')">
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        maxlength="255"
                        required
                        placeholder="Ej. Error al abrir el catálogo de la biblioteca"
                        class="h-11 w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                    >
                </x-field>

                <x-field label="Descripción detallada" :error="$errors->first('description')">
                    <textarea
                        name="description"
                        rows="6"
                        required
                        placeholder="Explica qué ocurrió, los pasos para reproducirlo y cualquier mensaje de error que hayas visto."
                        class="w-full resize-y rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 py-3 text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                    >{{ old('description') }}</textarea>
                </x-field>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-field label="Prioridad del impacto" :error="$errors->first('priority')">
                        <input type="hidden" id="create-priority-input" name="priority" value="{{ $selectedPriority }}">

                        <div class="relative" id="create-priority-dropdown">
                            <button
                                type="button"
                                id="create-priority-trigger"
                                onclick="toggleCreatePriority()"
                                aria-haspopup="listbox"
                                aria-expanded="false"
                                class="flex h-11 w-full items-center justify-between gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 text-left text-sm font-medium text-[var(--color-foreground)] hover:bg-[var(--color-muted)] focus:border-[var(--color-primary)] focus:outline-none transition-colors cursor-pointer"
                            >
                                <span id="create-priority-label" class="truncate">{{ $priorityOptions[$selectedPriority] ?? $priorityOptions['medium'] }}</span>
                                <x-icon name="chevron-down" id="create-priority-chevron" class="h-4 w-4 shrink-0 text-[var(--color-muted-foreground)] transition-transform duration-150" />
                            </button>

                            <div
                                id="create-priority-menu"
                                role="listbox"
                                class="hidden absolute left-0 right-0 z-50 mt-1.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] p-1.5 shadow-[var(--shadow-lg)]"
                            >
                                @foreach ($priorityOptions as $val => $text)
                                    @php $isSelected = $selectedPriority === $val; @endphp
                                    <button
                                        type="button"
                                        role="option"
                                        aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                        data-value="{{ $val }}"
                                        data-label="{{ $text }}"
                                        onclick="selectCreatePriority(this)"
                                        class="priority-opt flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs sm:text-sm transition-colors cursor-pointer {{ $isSelected ? 'bg-[var(--color-primary)]/10 font-semibold text-[var(--color-primary)]' : 'text-[var(--color-foreground)] hover:bg-[var(--color-muted)]' }}"
                                    >
                                        <span>{{ $text }}</span>
                                        <x-icon name="check" class="priority-check h-3.5 w-3.5 text-[var(--color-primary)] {{ $isSelected ? '' : 'hidden' }}" />
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </x-field>

                    <x-field label="ID de categoría" hint="Código de categoría que corresponde a tu reporte." :error="$errors->first('category_id')">
                        <input
                            type="text"
                            name="category_id"
                            value="{{ old('category_id') }}"
                            required
                            placeholder="CAT-0000"
                            class="h-11 w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-4 font-mono text-sm text-[var(--color-foreground)] focus:border-[var(--color-primary)] focus:outline-none"
                        >
                    </x-field>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <x-button type="submit" variant="primary" size="md">
                    <x-icon name="send" class="h-4 w-4" />
                    Crear ticket
                </x-button>
                <a
                    href="{{ route('support.my-tickets') }}"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-4 text-sm font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors"
                >
                    Cancelar
                </a>
            </div>
        </form>

        <aside class="space-y-4">
            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)]">
                <h2 class="text-sm font-bold text-[var(--color-foreground)] mb-3.5">Qué incluir en tu reporte</h2>
                <ul class="space-y-3 text-xs sm:text-sm text-[var(--color-muted-foreground)]">
                    <li class="flex gap-2.5">
                        <span class="mt-0.5 text-[var(--color-primary)] shrink-0"><x-icon name="check" class="h-4 w-4" /></span>
                        <span>Un título específico: evita títulos genéricos como “Ayuda” o “Error”.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="mt-0.5 text-[var(--color-primary)] shrink-0"><x-icon name="check" class="h-4 w-4" /></span>
                        <span>Los pasos exactos para reproducir el problema.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="mt-0.5 text-[var(--color-primary)] shrink-0"><x-icon name="check" class="h-4 w-4" /></span>
                        <span>El mensaje o código de error, si aparece en pantalla.</span>
                    </li>
                    <li class="flex gap-2.5">
                        <span class="mt-0.5 text-[var(--color-primary)] shrink-0"><x-icon name="check" class="h-4 w-4" /></span>
                        <span>Si el problema afecta a más usuarios o solo a tu cuenta.</span>
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl border border-[var(--color-primary)]/25 bg-[var(--color-primary)]/5 p-5">
                <div class="flex gap-3">
                    <span class="mt-0.5 shrink-0 text-[var(--color-primary)]"><x-icon name="info" class="h-5 w-5" /></span>
                    <div>
                        <p class="text-sm font-bold text-[var(--color-foreground)]">Sobre la prioridad</p>
                        <p class="mt-1 text-xs leading-relaxed text-[var(--color-muted-foreground)]">
                            La prioridad orienta al equipo de soporte para clasificar la cola de atención, pero no garantiza un tiempo de respuesta específico.
                        </p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-page>

<script>
    function toggleCreatePriority() {
        const menu = document.getElementById('create-priority-menu');
        const trigger = document.getElementById('create-priority-trigger');
        const chevron = document.getElementById('create-priority-chevron');
        const isOpen = !menu.classList.contains('hidden');

        menu.classList.toggle('hidden', isOpen);
        trigger.setAttribute('aria-expanded', String(!isOpen));
        chevron.classList.toggle('rotate-180', !isOpen);
    }

    function selectCreatePriority(btn) {
        document.getElementById('create-priority-input').value = btn.dataset.value;
        document.getElementById('create-priority-label').textContent = btn.dataset.label;

        document.querySelectorAll('.priority-opt').forEach(function (el) {
            el.classList.remove('bg-[var(--color-primary)]/10', 'font-semibold', 'text-[var(--color-primary)]');
            el.classList.add('text-[var(--color-foreground)]');
            el.setAttribute('aria-selected', 'false');
            const icon = el.querySelector('.priority-check');
            if (icon) icon.classList.add('hidden');
        });

        btn.classList.add('bg-[var(--color-primary)]/10', 'font-semibold', 'text-[var(--color-primary)]');
        btn.classList.remove('text-[var(--color-foreground)]');
        btn.setAttribute('aria-selected', 'true');
        const activeIcon = btn.querySelector('.priority-check');
        if (activeIcon) activeIcon.classList.remove('hidden');

        toggleCreatePriority();
    }

    document.addEventListener('click', function (e) {
        const dropdown = document.getElementById('create-priority-dropdown');
        const menu = document.getElementById('create-priority-menu');
        if (dropdown && !dropdown.contains(e.target) && !menu.classList.contains('hidden')) {
            toggleCreatePriority();
        }
    });
</script>
@endsection