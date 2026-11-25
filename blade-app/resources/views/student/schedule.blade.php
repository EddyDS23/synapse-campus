@extends('layouts.app')

@section('title', 'Mi Horario | Synapse')

@section('content')
@php
    $dayMap = [
        'monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles',
        'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado',
        'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'miércoles' => 'Miércoles',
        'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'sábado' => 'Sábado',
    ];
    $dayOrder = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6];
    $days = array_keys($dayOrder);

    $scheduleItems = collect($schedules)
        ->map(function ($schedule) use ($dayMap, $dayOrder) {
            $label = $dayMap[strtolower($schedule['day_of_week'] ?? '')] ?? ucfirst($schedule['day_of_week'] ?? '—');
            $schedule['day_label'] = $label;
            $schedule['day_index'] = $dayOrder[$label] ?? 99;
            return $schedule;
        })
        ->sortBy([['day_index', 'asc'], ['start_time', 'asc']])
        ->values();

    $activeDays = $scheduleItems
        ->pluck('day_label')
        ->filter(fn ($day) => isset($dayOrder[$day]))
        ->unique()
        ->sortBy(fn ($day) => $dayOrder[$day])
        ->values();
    $teacherCount = $scheduleItems->pluck('teacher')->filter()->unique()->count();
@endphp

<x-page>
    <header class="mb-8">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--color-muted-foreground)]">Académico</p>
        <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
            Mi horario
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
            Distribución semanal de tus clases, docentes y grupos asignados en el ciclo actual.
        </p>
    </header>

    @if (empty($schedules))
        <x-card>
            <x-empty-state
                title="Sin clases asignadas"
                description="Tu horario aparecerá aquí cuando el portal académico registre materias para el ciclo actual."
            />
        </x-card>
    @else
        <section aria-label="Resumen del horario" class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat label="Clases en el ciclo" :value="$scheduleItems->count()" tone="primary" />
            <x-stat label="Días con clase" :value="$activeDays->count()" tone="accent" />
            <x-stat label="Docentes" :value="$teacherCount" tone="success" />
        </section>

        <div class="mb-5 flex flex-wrap items-center gap-2" id="daySelector" role="group" aria-label="Filtrar por día">
            <button type="button" data-day="all" class="day-btn is-active inline-flex h-9 items-center rounded-full border border-[var(--color-primary)] bg-[var(--color-primary)] px-4 text-xs font-semibold text-[var(--color-on-primary)] transition">
                Ver todo
            </button>
            @foreach ($days as $day)
                @continue(!$activeDays->contains($day))
                <button type="button" data-day="{{ $day }}" class="day-btn inline-flex h-9 items-center rounded-full border border-[var(--color-border)] bg-[var(--color-card)] px-4 text-xs font-semibold text-[var(--color-foreground)] transition hover:border-[var(--color-primary)]/40 hover:bg-[var(--color-muted)]">
                    {{ $day }}
                </button>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3" id="scheduleGrid">
            @foreach ($scheduleItems as $schedule)
                <article
                    class="schedule-item flex flex-col rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)] transition hover:border-[var(--color-primary)]/30 hover:shadow-[var(--shadow-md)]"
                    data-day="{{ $schedule['day_label'] }}"
                >
                    <div class="flex items-start justify-between gap-3">
                        <x-badge variant="primary">{{ $schedule['day_label'] }}</x-badge>
                        <p class="text-right font-mono text-sm font-semibold leading-tight text-[var(--color-foreground)]">
                            {{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }}
                            <span class="block text-[11px] font-normal text-[var(--color-muted-foreground)]">
                                a {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}
                            </span>
                        </p>
                    </div>

                    <h2 class="mt-4 font-[family-name:var(--font-serif)] text-xl font-semibold leading-snug text-[var(--color-foreground)]">
                        {{ $schedule['subject_name'] }}
                    </h2>

                    <dl class="mt-auto grid gap-3 border-t border-[var(--color-border)] pt-4 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[var(--radius-sm)] bg-[var(--color-muted)] text-[var(--color-muted-foreground)]">
                                <x-icon name="user" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Docente</dt>
                                <dd class="truncate text-[var(--color-foreground)]">{{ $schedule['teacher'] ?: 'Por asignar' }}</dd>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[var(--radius-sm)] bg-[var(--color-muted)] text-[var(--color-muted-foreground)]">
                                <x-icon name="users" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-[var(--color-muted-foreground)]">Grupo / Aula</dt>
                                <dd class="truncate text-[var(--color-foreground)]">{{ $schedule['group'] ?: 'Por asignar' }}</dd>
                            </div>
                        </div>
                    </dl>
                </article>
            @endforeach
        </div>

        <p id="noDayResults" class="hidden rounded-[var(--radius-lg)] border border-dashed border-[var(--color-border)] bg-[var(--color-muted)]/40 px-6 py-10 text-center text-sm text-[var(--color-muted-foreground)]">
            No hay clases registradas para este día.
        </p>
    @endif
</x-page>

@if (!empty($schedules))
<script>
    (function () {
        const buttons = document.querySelectorAll('.day-btn');
        const items = document.querySelectorAll('.schedule-item');
        const empty = document.getElementById('noDayResults');

        function filter(day) {
            let visible = 0;
            items.forEach(item => {
                const match = day === 'all' || item.dataset.day === day;
                item.classList.toggle('hidden', !match);
                if (match) visible++;
            });
            empty.classList.toggle('hidden', visible > 0);

            buttons.forEach(btn => {
                const active = btn.dataset.day === day;
                btn.classList.toggle('is-active', active);
                btn.classList.toggle('border-[var(--color-primary)]', active);
                btn.classList.toggle('bg-[var(--color-primary)]', active);
                btn.classList.toggle('text-[var(--color-on-primary)]', active);
                btn.classList.toggle('border-[var(--color-border)]', !active);
                btn.classList.toggle('bg-[var(--color-card)]', !active);
                btn.classList.toggle('text-[var(--color-foreground)]', !active);
            });
        }

        buttons.forEach(btn => btn.addEventListener('click', () => filter(btn.dataset.day)));
    })();
</script>
@endif
@endsection
