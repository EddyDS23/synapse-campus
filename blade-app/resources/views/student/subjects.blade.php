@extends('layouts.app')

@section('title', 'Mis Materias | Synapse Campus')

@section('content')
@php
    $semesters = collect($career_subjects ?? [])
        ->filter(fn ($item) => is_array($item))
        ->map(function (array $semester) {
            $subjects = collect($semester['subjects'] ?? [])
                ->filter(fn ($sub) => is_array($sub))
                ->values()
                ->all();

            return [
                'semester'      => (int) ($semester['semester'] ?? 0),
                'subjects'      => $subjects,
                'total_credits' => collect($subjects)->sum(fn ($sub) => (int) ($sub['credits'] ?? 0)),
            ];
        })
        ->filter(fn ($s) => $s['semester'] > 0)
        ->sortBy('semester')
        ->values();

    $totalSubjects = $semesters->sum(fn ($s) => count($s['subjects']));
    $totalCredits  = $semesters->sum('total_credits');

    $dashboardUrl = \Illuminate\Support\Facades\Route::has('dashboard')
        ? route('dashboard')
        : url('/dashboard');
@endphp

<x-page>
    <x-breadcrumb :items="[
        ['label' => 'Inicio', 'url' => $dashboardUrl],
        ['label' => 'Académico'],
        ['label' => 'Mapa curricular'],
    ]" />

    {{-- Cabecera adaptable a Light / Dark Mode --}}
    <header class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 sm:p-7 shadow-[var(--shadow-sm)]">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-5 h-1 rounded-full bg-[var(--color-primary)]"></span>
            <span class="text-xs font-bold uppercase tracking-widest text-[var(--color-muted-foreground)]">Portal Estudiantil · Académico</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[var(--color-foreground)] tracking-tight">
            Mapa curricular
        </h1>
        <p class="text-sm sm:text-base text-[var(--color-muted-foreground)] mt-1 max-w-2xl">
            Asignaturas que integran tu plan de estudios, organizadas por semestre y carga crediticia.
        </p>
    </header>

    @if ($semesters->isEmpty())
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-8 shadow-[var(--shadow-sm)]">
            <x-empty-state
                title="Sin materias registradas"
                description="El mapa curricular aparecerá aquí cuando el portal estudiantil devuelva tu plan de estudios."
            />
        </div>
    @else
        {{-- Indicadores clave --}}
        <section aria-label="Resumen curricular" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat label="Semestres del plan" :value="$semesters->count()" tone="primary" />
            <x-stat label="Asignaturas" :value="$totalSubjects" tone="warning" />
            <x-stat label="Créditos totales" :value="$totalCredits" tone="success" />
        </section>

        {{-- Barra de filtrado por semestre --}}
        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-4 sm:px-5 shadow-[var(--shadow-sm)] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <span class="text-xs font-bold uppercase tracking-wider text-[var(--color-muted-foreground)] shrink-0">
                Filtrar semestre
            </span>

            <div class="flex flex-wrap items-center gap-2" id="semesterSelector" role="group" aria-label="Seleccionar semestre">
                <button
                    type="button"
                    data-semester="all"
                    aria-pressed="true"
                    class="semester-btn inline-flex h-9 items-center justify-center rounded-xl border border-[var(--color-primary)] bg-[var(--color-primary)] px-3.5 text-xs font-semibold text-white transition-colors cursor-pointer"
                >
                    Todos
                </button>

                @foreach ($semesters as $index => $semester)
                    @php $semNum = (string) $semester['semester']; @endphp
                    <button
                        type="button"
                        data-semester="{{ $semNum }}"
                        aria-pressed="false"
                        class="semester-btn inline-flex h-9 min-w-[2.4rem] items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-muted)]/50 px-3.5 text-xs font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors cursor-pointer"
                    >
                        {{ $semNum  }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Bloques por semestre --}}
        <div class="space-y-6" id="semestersContainer">
            @foreach ($semesters as $semester)
                @php
                    $semNum     = (string) $semester['semester'];
                    $subjects   = $semester['subjects'];
                    $semCredits = $semester['total_credits'];
                @endphp

                <section
                    id="semester-{{ $semNum }}"
                    data-semester-panel="{{ $semNum }}"
                    class="semester-panel rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] shadow-[var(--shadow-sm)] overflow-hidden"
                    aria-label="Semestre {{ $semNum }}"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--color-border)] bg-[var(--color-muted)]/50 px-6 py-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-4 h-1 rounded-full bg-[var(--color-primary)]"></span>
                            <h2 class="text-sm sm:text-base font-bold text-[var(--color-foreground)]">
                                Semestre {{ $semNum }}
                            </h2>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="inline-flex items-center rounded-lg border border-[var(--color-border)] bg-[var(--color-card)] px-2.5 py-1 font-mono font-semibold text-[var(--color-muted-foreground)]">
                                {{ count($subjects) }} {{ count($subjects) === 1 ? 'materia' : 'materias' }}
                            </span>
                            <span class="inline-flex items-center rounded-lg border border-[var(--color-primary)]/25 bg-[var(--color-primary)]/10 px-2.5 py-1 font-mono font-bold text-[var(--color-primary)]">
                                {{ $semCredits }} créditos
                            </span>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">
                        @if (empty($subjects))
                            <p class="text-xs sm:text-sm text-[var(--color-muted-foreground)]">
                                No hay asignaturas registradas en este semestre.
                            </p>
                        @else
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                @foreach ($subjects as $subject)
                                    @php
                                        $code    = $subject['code'] ?? 'S/C';
                                        $name    = $subject['name'] ?? 'Asignatura sin nombre';
                                        $credits = (int) ($subject['credits'] ?? 0);
                                    @endphp

                                    <article class="flex flex-col justify-between rounded-xl border border-[var(--color-border)] border-l-[3px] border-l-[var(--color-primary)] bg-[var(--color-muted)]/35 p-4 sm:p-5 hover:bg-[var(--color-muted)]/65 transition-colors">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="inline-flex items-center rounded-lg border border-[var(--color-border)] bg-[var(--color-card)] px-2.5 py-1 font-mono text-xs font-bold text-[var(--color-foreground)]">
                                                {{ $code }}
                                            </span>

                                            <span class="inline-flex items-center rounded-lg border border-[var(--color-primary)]/25 bg-[var(--color-primary)]/10 px-2.5 py-0.5 font-mono text-xs font-bold text-[var(--color-primary)]">
                                                {{ $credits }} {{ $credits === 1 ? 'crédito' : 'créditos' }}
                                            </span>
                                        </div>

                                        <h3 class="mt-3.5 text-sm sm:text-base font-bold leading-snug text-[var(--color-foreground)]">
                                            {{ $name }}
                                        </h3>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</x-page>

@if ($semesters->isNotEmpty())
<script>
    (function () {
        const buttons = document.querySelectorAll('.semester-btn');
        const panels  = document.querySelectorAll('.semester-panel');

        const activeBtnClasses   = ['border-[var(--color-primary)]', 'bg-[var(--color-primary)]', 'text-white'];
        const inactiveBtnClasses = ['border-[var(--color-border)]', 'bg-[var(--color-muted)]/50', 'text-[var(--color-foreground)]', 'hover:bg-[var(--color-muted)]'];

        function filterSemester(semester) {
            panels.forEach(function (panel) {
                const match = semester === 'all' || panel.dataset.semesterPanel === String(semester);
                panel.classList.toggle('hidden', !match);
            });

            buttons.forEach(function (btn) {
                const isSelected = btn.dataset.semester === String(semester);
                btn.setAttribute('aria-pressed', String(isSelected));

                if (isSelected) {
                    btn.classList.remove(...inactiveBtnClasses);
                    btn.classList.add(...activeBtnClasses);
                } else {
                    btn.classList.remove(...activeBtnClasses);
                    btn.classList.add(...inactiveBtnClasses);
                }
            });
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterSemester(btn.dataset.semester);
            });
        });
    })();
</script>
@endif
@endsection