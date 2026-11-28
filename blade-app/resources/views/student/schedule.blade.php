@extends('layouts.app')

@section('title', 'Mi Horario | Synapse Campus')

@section('content')
@php
    $dayMap = [
        'monday'    => 'Lunes',     'tuesday'   => 'Martes',    'wednesday' => 'Miércoles',
        'thursday'  => 'Jueves',    'friday'    => 'Viernes',   'saturday'  => 'Sábado',
        'sunday'    => 'Domingo',
        'lunes'     => 'Lunes',     'martes'    => 'Martes',    'miercoles' => 'Miércoles', 'miércoles' => 'Miércoles',
        'jueves'    => 'Jueves',    'viernes'   => 'Viernes',   'sabado'    => 'Sábado',    'sábado'    => 'Sábado',
        'domingo'   => 'Domingo',
    ];
    $dayOrder = [
        'Lunes'     => 1,
        'Martes'    => 2,
        'Miércoles' => 3,
        'Jueves'    => 4,
        'Viernes'   => 5,
        'Sábado'    => 6,
    ];

    $todayKey   = strtolower(now()->englishDayOfWeek);
    $todayLabel = $dayMap[$todayKey] ?? null;

    $toMinutes = static function (?string $time): ?int {
        if (empty($time)) {
            return null;
        }
        try {
            $parsed = \Carbon\Carbon::parse($time);
            return ($parsed->hour * 60) + $parsed->minute;
        } catch (\Throwable) {
            return null;
        }
    };

    $scheduleItems = collect($schedules ?? [])
        ->filter(fn ($item) => is_array($item))
        ->map(function (array $schedule) use ($dayMap, $dayOrder, $toMinutes) {
            $rawDay   = strtolower(trim((string) ($schedule['day_of_week'] ?? '')));
            $label    = $dayMap[$rawDay] ?? ($rawDay !== '' ? ucfirst($rawDay) : 'Sin día');
            $startMin = $toMinutes($schedule['start_time'] ?? null);
            $endMin   = $toMinutes($schedule['end_time'] ?? null);

            if ($startMin !== null && ($endMin === null || $endMin <= $startMin)) {
                $endMin = $startMin + 60;
            }

            $schedule['day_label'] = $label;
            $schedule['day_index'] = $dayOrder[$label] ?? 99;
            $schedule['start_min'] = $startMin;
            $schedule['end_min']   = $endMin;

            return $schedule;
        })
        ->sortBy([['day_index', 'asc'], ['start_min', 'asc']])
        ->values();

    $hasSaturday = $scheduleItems->contains(fn ($item) => $item['day_label'] === 'Sábado');
    $tableDays   = $hasSaturday
        ? ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
        : ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

    $activeDays = $scheduleItems
        ->pluck('day_label')
        ->filter(fn ($day) => isset($dayOrder[$day]))
        ->unique()
        ->sortBy(fn ($day) => $dayOrder[$day])
        ->values();

    $itemsByDay   = $scheduleItems->groupBy('day_label');
    $countsByDay  = $scheduleItems->countBy('day_label');
    $teacherCount = $scheduleItems->pluck('teacher')->filter()->unique()->count();

    $usesHalfHours = $scheduleItems->contains(function ($item) {
        return ($item['start_min'] !== null && $item['start_min'] % 60 !== 0)
            || ($item['end_min'] !== null && $item['end_min'] % 60 !== 0);
    });
    $stepMinutes = $usesHalfHours ? 30 : 60;

    $validStarts = $scheduleItems->pluck('start_min')->filter(fn ($v) => $v !== null);
    $validEnds   = $scheduleItems->pluck('end_min')->filter(fn ($v) => $v !== null);

    $minMinute = $validStarts->isNotEmpty()
        ? intdiv((int) $validStarts->min(), 60) * 60
        : 7 * 60;

    $maxMinute = $validEnds->isNotEmpty()
        ? (int) ceil((int) $validEnds->max() / 60) * 60
        : 15 * 60;

    if ($maxMinute <= $minMinute) {
        $maxMinute = $minMinute + 60;
    }

    $timeSlots = [];
    for ($m = $minMinute; $m < $maxMinute; $m += $stepMinutes) {
        $nextM = $m + $stepMinutes;
        $timeSlots[] = [
            'start'     => $m,
            'end'       => $nextM,
            'label'     => sprintf('%02d:%02d', intdiv($m, 60), $m % 60),
            'end_label' => sprintf('%02d:%02d', intdiv($nextM, 60), $nextM % 60),
            'is_hour'   => ($m % 60 === 0),
        ];
    }

    $rawBuckets = [];
    foreach ($scheduleItems as $item) {
        $day = $item['day_label'];
        if (!in_array($day, $tableDays, true) || $item['start_min'] === null) {
            continue;
        }
        $snappedStart = intdiv($item['start_min'], $stepMinutes) * $stepMinutes;
        $rawSpan      = max(1, (int) ceil(($item['end_min'] - $snappedStart) / $stepMinutes));
        $maxAvailSpan = max(1, intdiv($maxMinute - $snappedStart, $stepMinutes));

        $rawBuckets[$day][$snappedStart][] = [
            'item' => $item,
            'span' => min($rawSpan, $maxAvailSpan),
        ];
    }

    $renderCells = [];
    $skipCells   = [];
    foreach ($tableDays as $day) {
        $occupiedUntil = 0;
        foreach ($timeSlots as $slot) {
            $m = $slot['start'];
            if ($m < $occupiedUntil) {
                $skipCells[$day][$m] = true;
                continue;
            }
            if (!empty($rawBuckets[$day][$m])) {
                $entries = $rawBuckets[$day][$m];
                $rowSpan = collect($entries)->max('span');
                $renderCells[$day][$m] = [
                    'entries' => $entries,
                    'rowspan' => $rowSpan,
                ];
                $occupiedUntil = $m + ($rowSpan * $stepMinutes);
            }
        }
    }

    $dashboardUrl = \Illuminate\Support\Facades\Route::has('dashboard')
        ? route('dashboard')
        : url('/dashboard');
@endphp

<x-page>
    <x-breadcrumb :items="[
        ['label' => 'Inicio', 'url' => $dashboardUrl],
        ['label' => 'Académico'],
        ['label' => 'Mi horario'],
    ]" />

    {{-- Cabecera adaptable a Light / Dark Mode --}}
    <header class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 sm:p-7 shadow-[var(--shadow-sm)]">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Portal Estudiantil · Académico</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
            Mi horario semanal
        </h1>
        <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1 max-w-2xl">
            Matriz horaria completa con tus asignaturas, bloques horarios, aulas y docentes asignados.
        </p>
    </header>

    @if ($scheduleItems->isEmpty())
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-8 shadow-[var(--shadow-sm)]">
            <x-empty-state
                title="Sin clases asignadas"
                description="Tu horario aparecerá aquí cuando el portal estudiantil registre materias para el ciclo actual."
            />
        </div>
    @else
        {{-- Indicadores clave --}}
        <section aria-label="Resumen del horario" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat label="Clases en el ciclo" :value="$scheduleItems->count()" tone="primary" />
            <x-stat label="Días con clase" :value="$activeDays->count()" tone="warning" />
            <x-stat label="Docentes asignados" :value="$teacherCount" tone="success" />
        </section>

        {{-- Barra de filtrado por día --}}
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-4 sm:px-5 shadow-[var(--shadow-sm)] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                Filtrar vista
            </span>

            <div class="flex flex-wrap items-center gap-2" id="daySelector" role="group" aria-label="Filtrar vista del horario">
                <button
                    type="button"
                    data-day="all"
                    aria-pressed="true"
                    class="day-btn inline-flex h-9 items-center gap-1.5 rounded-xl border border-[var(--color-primary)] bg-[var(--color-primary)] px-3.5 text-xs font-semibold text-white transition-colors cursor-pointer"
                >
                    <span>Semana completa</span>
                    <span class="day-count inline-flex min-w-[1.35rem] items-center justify-center rounded-full border border-transparent bg-white/20 px-1.5 py-0.5 font-mono text-[11px] leading-none text-white">
                        {{ $scheduleItems->count() }}
                    </span>
                </button>

                @foreach ($tableDays as $day)
                    @php
                        $isToday  = $todayLabel === $day;
                        $dayCount = $countsByDay[$day] ?? 0;
                    @endphp
                    <button
                        type="button"
                        data-day="{{ $day }}"
                        aria-pressed="false"
                        class="day-btn inline-flex h-9 items-center gap-1.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-3.5 text-xs font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors cursor-pointer"
                    >
                        <span>{{ $day }}</span>
                        @if ($isToday)
                            <span class="today-dot h-1.5 w-1.5 rounded-full bg-[var(--color-primary)]" title="Hoy"></span>
                        @endif
                        <span class="day-count inline-flex min-w-[1.35rem] items-center justify-center rounded-full border border-[var(--color-border)] bg-[var(--color-card)] px-1.5 py-0.5 font-mono text-[11px] leading-none text-[var(--color-muted-foreground)]">
                            {{ $dayCount }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- MODO 1: Matriz Horaria Semanal (Semana completa) --}}
        <section id="weeklyMatrix" aria-label="Tabla semanal de clases" class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] border-collapse table-fixed">
                    <thead>
                        <tr class="bg-[var(--color-muted)]/70 border-b border-[var(--color-border)] text-left">
                            <th scope="col" class="sticky left-0 z-20 w-24 bg-[var(--color-muted)] border-r border-[var(--color-border)] px-3.5 py-3.5 text-[11px] font-bold uppercase tracking-wider text-[var(--color-muted-foreground)]">
                                Hora
                            </th>

                            @foreach ($tableDays as $day)
                                @php
                                    $isTodayCol = $todayLabel === $day;
                                    $dayCount   = $countsByDay[$day] ?? 0;
                                @endphp
                                <th
                                    scope="col"
                                    class="px-3.5 py-3.5 border-r last:border-r-0 border-[var(--color-border)] {{ $isTodayCol ? 'bg-[var(--color-primary)]/[0.06]' : '' }}"
                                >
                                    <button
                                        type="button"
                                        onclick="window.selectScheduleDay('{{ $day }}')"
                                        title="Ver agenda de {{ $day }}"
                                        class="w-full flex items-center justify-between gap-2 text-left group cursor-pointer"
                                    >
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="text-xs sm:text-sm font-bold tracking-tight text-[var(--color-foreground)] group-hover:text-[var(--color-primary)] transition-colors truncate">
                                                {{ $day }}
                                            </span>
                                            @if ($isTodayCol)
                                                <span class="rounded-md bg-[var(--color-primary)] px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white shrink-0">
                                                    Hoy
                                                </span>
                                            @endif
                                        </div>

                                        <span class="inline-flex min-w-[1.35rem] items-center justify-center rounded-full border border-[var(--color-border)] bg-[var(--color-card)] px-1.5 py-0.5 font-mono text-[11px] font-semibold leading-none text-[var(--color-muted-foreground)] shrink-0">
                                            {{ $dayCount }}
                                        </span>
                                    </button>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[var(--color-border)]">
                        @foreach ($timeSlots as $slot)
                            @php $m = $slot['start']; @endphp
                            <tr class="{{ $slot['is_hour'] ? 'border-t border-[var(--color-border)]' : 'border-t border-dashed border-[var(--color-border)]/60' }}">
                                <th scope="row" class="sticky left-0 z-10 w-24 bg-[var(--color-muted)] border-r border-[var(--color-border)] px-3.5 py-3 text-left align-top select-none">
                                    <span class="block font-mono text-xs font-bold text-[var(--color-foreground)]">
                                        {{ $slot['label'] }}
                                    </span>
                                    <span class="block font-mono text-[10px] text-[var(--color-muted-foreground)] mt-0.5">
                                        {{ $slot['end_label'] }}
                                    </span>
                                </th>

                                @foreach ($tableDays as $day)
                                    @continue(!empty($skipCells[$day][$m]))

                                    @php
                                        $isTodayCol = $todayLabel === $day;
                                        $cell       = $renderCells[$day][$m] ?? null;
                                    @endphp

                                    @if ($cell)
                                        <td
                                            rowspan="{{ $cell['rowspan'] }}"
                                            class="p-1.5 align-top border-r last:border-r-0 border-[var(--color-border)] {{ $isTodayCol ? 'bg-[var(--color-primary)]/[0.03]' : '' }}"
                                        >
                                            <div class="space-y-1.5 h-full">
                                                @foreach ($cell['entries'] as $entry)
                                                    @php
                                                        $schedule    = $entry['item'];
                                                        $startTime   = !empty($schedule['start_time']) ? \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') : $slot['label'];
                                                        $endTime     = !empty($schedule['end_time']) ? \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') : $slot['end_label'];
                                                        $subjectName = $schedule['subject_name'] ?? 'Asignatura sin nombre';
                                                        $teacherName = !empty($schedule['teacher']) ? $schedule['teacher'] : 'Por asignar';
                                                        $groupName   = !empty($schedule['group']) ? $schedule['group'] : null;
                                                    @endphp

                                                    <article class="rounded-xl border border-[var(--color-border)] border-l-[3px] border-l-[var(--color-primary)] bg-[var(--color-muted)]/55 p-2.5 hover:bg-[var(--color-muted)] transition-colors">
                                                        <div class="flex items-center justify-between gap-1.5 text-[11px]">
                                                            <span class="font-mono font-bold text-[var(--color-primary)]">
                                                                {{ $startTime }}–{{ $endTime }}
                                                            </span>

                                                            @if ($groupName)
                                                                <span class="truncate max-w-[5.25rem] rounded-md border border-[var(--color-border)] bg-[var(--color-card)] px-1.5 py-0.5 font-mono text-[10px] font-semibold text-[var(--color-muted-foreground)]" title="{{ $groupName }}">
                                                                    {{ $groupName }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <h2 class="mt-1 text-xs font-bold leading-snug text-[var(--color-foreground)] line-clamp-2" title="{{ $subjectName }}">
                                                            {{ $subjectName }}
                                                        </h2>

                                                        <div class="mt-1.5 pt-1.5 border-t border-[var(--color-border)]/70 flex items-center gap-1.5 text-[11px] text-[var(--color-muted-foreground)]">
                                                            <x-icon name="user" class="h-3 w-3 shrink-0" />
                                                            <span class="truncate" title="{{ $teacherName }}">{{ $teacherName }}</span>
                                                        </div>
                                                    </article>
                                                @endforeach
                                            </div>
                                        </td>
                                    @else
                                        <td class="h-14 p-1.5 border-r last:border-r-0 border-[var(--color-border)] {{ $isTodayCol ? 'bg-[var(--color-primary)]/[0.02]' : '' }}"></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- MODO 2: Agenda Cronológica Compacta (al seleccionar un día específico) --}}
        <section id="dayAgenda" aria-label="Agenda del día seleccionado" class="hidden">
            @foreach ($tableDays as $day)
                @php
                    $dayClasses = $itemsByDay->get($day, collect());
                    $isTodayDay = $todayLabel === $day;
                    $firstStart = $dayClasses->isNotEmpty() && !empty($dayClasses->first()['start_time'])
                        ? \Carbon\Carbon::parse($dayClasses->first()['start_time'])->format('H:i')
                        : null;
                    $lastEnd = $dayClasses->isNotEmpty()
                        ? $dayClasses->pluck('end_time')->filter()->map(fn ($t) => \Carbon\Carbon::parse($t)->format('H:i'))->max()
                        : null;
                @endphp

                <div data-agenda-day="{{ $day }}" class="day-agenda-panel hidden max-w-4xl mx-auto rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] overflow-hidden">
                    {{-- Encabezado adaptable del día --}}
                    <div class="bg-[var(--color-muted)]/60 border-b border-[var(--color-border)] px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-4 h-1 rounded-full bg-[var(--color-primary)]"></span>
                            <h2 class="text-lg sm:text-xl font-bold text-[var(--color-foreground)] tracking-tight">{{ $day }}</h2>
                            @if ($isTodayDay)
                                <span class="rounded-md bg-[var(--color-primary)] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                    Hoy
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            @if ($dayClasses->isNotEmpty())
                                @if ($firstStart && $lastEnd)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--color-border)] bg-[var(--color-card)] px-2.5 py-1 font-mono font-semibold text-[var(--color-foreground)]">
                                        <x-icon name="clock" class="h-3.5 w-3.5 text-[var(--color-primary)]" />
                                        {{ $firstStart }} – {{ $lastEnd }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center rounded-lg border border-[var(--color-border)] bg-[var(--color-card)] px-2.5 py-1 font-mono font-semibold text-[var(--color-muted-foreground)]">
                                    {{ $dayClasses->count() }} {{ $dayClasses->count() === 1 ? 'clase' : 'clases' }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-lg border border-[var(--color-border)] bg-[var(--color-card)] px-2.5 py-1 font-semibold text-[var(--color-muted-foreground)]">
                                    Sin actividad
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Lista cronológica de bloques del día --}}
                    @if ($dayClasses->isEmpty())
                        <div class="p-8">
                            <x-empty-state
                                title="Día libre"
                                description="No tienes clases programadas para el {{ strtolower($day) }}."
                            />
                        </div>
                    @else
                        <div class="p-5 sm:p-6 space-y-3">
                            @foreach ($dayClasses as $schedule)
                                @php
                                    $startTime   = !empty($schedule['start_time']) ? \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') : '—';
                                    $endTime     = !empty($schedule['end_time']) ? \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') : '—';
                                    $subjectName = $schedule['subject_name'] ?? 'Asignatura sin nombre';
                                    $teacherName = !empty($schedule['teacher']) ? $schedule['teacher'] : 'Por asignar';
                                    $groupName   = !empty($schedule['group']) ? $schedule['group'] : 'Por asignar';

                                    $durationMin = ($schedule['start_min'] !== null && $schedule['end_min'] !== null)
                                        ? max(0, $schedule['end_min'] - $schedule['start_min'])
                                        : 60;
                                    $durationLabel = $durationMin % 60 === 0
                                        ? ($durationMin / 60) . ' h'
                                        : $durationMin . ' min';
                                @endphp

                                <article class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-5 rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/40 p-4 hover:bg-[var(--color-muted)]/70 transition-colors">
                                    {{-- Columna izquierda: Bloque horario y duración --}}
                                    <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center sm:w-28 shrink-0 text-right">
                                        <div>
                                            <span class="font-mono text-sm sm:text-base font-bold text-[var(--color-foreground)]">
                                                {{ $startTime }}
                                            </span>
                                            <span class="font-mono text-xs text-[var(--color-muted-foreground)] sm:block">
                                                a {{ $endTime }}
                                            </span>
                                        </div>

                                        <span class="inline-flex items-center rounded-md border border-[var(--color-primary)]/25 bg-[var(--color-primary)]/10 px-2 py-0.5 font-mono text-[11px] font-bold text-[var(--color-primary)] sm:mt-1.5">
                                            {{ $durationLabel }}
                                        </span>
                                    </div>

                                    {{-- Separador vertical Crimson --}}
                                    <div class="hidden sm:block w-1 self-stretch rounded-full bg-[var(--color-primary)] shrink-0"></div>

                                    {{-- Columna derecha: Materia, Docente y Aula --}}
                                    <div class="min-w-0 flex-1 border-t sm:border-t-0 pt-3 sm:pt-0 border-[var(--color-border)]">
                                        <h3 class="text-base font-bold text-[var(--color-foreground)] leading-snug">
                                            {{ $subjectName }}
                                        </h3>

                                        <div class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-[var(--color-muted-foreground)]">
                                            <span class="inline-flex items-center gap-1.5 font-medium text-[var(--color-foreground)]">
                                                <x-icon name="user" class="h-3.5 w-3.5 text-[var(--color-primary)] shrink-0" />
                                                <span>{{ $teacherName }}</span>
                                            </span>

                                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-[var(--color-border)] bg-[var(--color-card)] px-2.5 py-0.5 font-mono font-semibold text-[var(--color-foreground)]">
                                                <x-icon name="users" class="h-3.5 w-3.5 text-[var(--color-muted-foreground)] shrink-0" />
                                                <span>{{ $groupName }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    @endif
</x-page>

@if ($scheduleItems->isNotEmpty())
<script>
    (function () {
        const buttons      = document.querySelectorAll('.day-btn');
        const weeklyMatrix = document.getElementById('weeklyMatrix');
        const dayAgenda    = document.getElementById('dayAgenda');
        const agendaPanels = document.querySelectorAll('.day-agenda-panel');

        const activeBtnClasses     = ['border-[var(--color-primary)]', 'bg-[var(--color-primary)]', 'text-white'];
        const inactiveBtnClasses   = ['border-[var(--color-border)]', 'bg-[var(--color-muted)]/50', 'text-[var(--color-foreground)]', 'hover:bg-[var(--color-muted)]'];
        const activeCountClasses   = ['border-transparent', 'bg-white/20', 'text-white'];
        const inactiveCountClasses = ['border-[var(--color-border)]', 'bg-[var(--color-card)]', 'text-[var(--color-muted-foreground)]'];

        window.selectScheduleDay = function (day) {
            if (day === 'all') {
                if (weeklyMatrix) weeklyMatrix.classList.remove('hidden');
                if (dayAgenda) dayAgenda.classList.add('hidden');
            } else {
                if (weeklyMatrix) weeklyMatrix.classList.add('hidden');
                if (dayAgenda) dayAgenda.classList.remove('hidden');

                agendaPanels.forEach(function (panel) {
                    panel.classList.toggle('hidden', panel.dataset.agendaDay !== day);
                });
            }

            buttons.forEach(function (btn) {
                const isSelected = btn.dataset.day === day;
                const countBadge = btn.querySelector('.day-count');
                const todayDot   = btn.querySelector('.today-dot');

                btn.setAttribute('aria-pressed', String(isSelected));

                if (isSelected) {
                    btn.classList.remove(...inactiveBtnClasses);
                    btn.classList.add(...activeBtnClasses);
                    if (countBadge) {
                        countBadge.classList.remove(...inactiveCountClasses);
                        countBadge.classList.add(...activeCountClasses);
                    }
                    if (todayDot) {
                        todayDot.classList.remove('bg-[var(--color-primary)]');
                        todayDot.classList.add('bg-white');
                    }
                } else {
                    btn.classList.remove(...activeBtnClasses);
                    btn.classList.add(...inactiveBtnClasses);
                    if (countBadge) {
                        countBadge.classList.remove(...activeCountClasses);
                        countBadge.classList.add(...inactiveCountClasses);
                    }
                    if (todayDot) {
                        todayDot.classList.remove('bg-white');
                        todayDot.classList.add('bg-[var(--color-primary)]');
                    }
                }
            });
        };

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                window.selectScheduleDay(btn.dataset.day);
            });
        });
    })();
</script>
@endif
@endsection