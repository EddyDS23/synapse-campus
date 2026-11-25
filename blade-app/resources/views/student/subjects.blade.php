@extends('layouts.app')

@section('title', 'Mis Materias | Synapse')

@section('content')
@php
    $semesters = collect($career_subjects);
    $totalSubjects = $semesters->sum(fn ($semester) => count($semester['subjects'] ?? []));
    $totalCredits = $semesters->sum(fn ($semester) => collect($semester['subjects'] ?? [])->sum('credits'));
@endphp

<x-page>
    <header class="mb-8">
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--color-muted-foreground)]">Académico</p>
        <h1 class="mt-2 text-3xl font-semibold leading-tight tracking-tight text-[var(--color-foreground)] font-[family-name:var(--font-serif)]">
            Mapa curricular
        </h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-muted-foreground)]">
            Materias que integran tu plan de estudios, organizadas por semestre y con sus créditos registrados.
        </p>
    </header>

    @if ($semesters->isEmpty())
        <x-card>
            <x-empty-state
                title="Sin materias registradas"
                description="El mapa curricular aparecerá cuando el portal académico devuelva tu plan de estudios."
            />
        </x-card>
    @else
        <section aria-label="Resumen curricular" class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat label="Semestres" :value="$semesters->count()" tone="primary" />
            <x-stat label="Materias" :value="$totalSubjects" tone="accent" />
            <x-stat label="Créditos totales" :value="$totalCredits" tone="success" />
        </section>

        <div class="mb-6">
            <label for="semesterSelect" class="mb-2 block text-xs font-semibold text-[var(--color-foreground)] lg:hidden">Semestre</label>
            <select
                id="semesterSelect"
                class="mb-4 h-10 w-full rounded-[var(--radius-md)] border border-[var(--color-border)] bg-[var(--color-card)] px-3 text-sm text-[var(--color-foreground)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)] lg:hidden"
            >
                @foreach ($semesters as $semester)
                    <option value="{{ $semester['semester'] }}">
                        Semestre {{ $semester['semester'] }} · {{ count($semester['subjects'] ?? []) }} materias
                    </option>
                @endforeach
            </select>

            <div class="hidden flex-wrap gap-2 lg:flex" role="group" aria-label="Seleccionar semestre">
                @foreach ($semesters as $index => $semester)
                    <button
                        type="button"
                        data-semester="{{ $semester['semester'] }}"
                        class="semester-btn inline-flex h-9 items-center rounded-full border px-4 text-xs font-semibold transition {{ $index === 0
                            ? 'border-[var(--color-primary)] bg-[var(--color-primary)] text-[var(--color-on-primary)]'
                            : 'border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-foreground)] hover:bg-[var(--color-muted)]' }}"
                    >
                        Semestre {{ $semester['semester'] }}
                    </button>
                @endforeach
            </div>
        </div>

        @foreach ($semesters as $index => $semester)
            <section
                id="semester-{{ $semester['semester'] }}"
                class="semester-panel {{ $index === 0 ? '' : 'hidden' }}"
                aria-label="Semestre {{ $semester['semester'] }}"
            >
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-[0.14em] text-[var(--color-muted-foreground)]">
                    Semestre {{ $semester['semester'] }}
                    <span class="ml-2 font-normal normal-case tracking-normal">· {{ count($semester['subjects'] ?? []) }} materias</span>
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($semester['subjects'] as $subject)
                        <article class="flex flex-col rounded-[var(--radius-lg)] border border-[var(--color-border)] border-l-4 border-l-[var(--color-primary)] bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)] transition hover:border-l-[var(--color-accent)] hover:shadow-[var(--shadow-md)]">
                            <div class="flex items-start justify-between gap-3">
                                <x-badge variant="primary">{{ $subject['code'] }}</x-badge>
                                <span class="inline-flex items-center gap-1 rounded-full border border-[var(--color-accent)]/25 bg-[color-mix(in_srgb,var(--color-accent)_10%,transparent)] px-2.5 py-0.5 text-[11px] font-semibold text-[var(--color-accent)]">
                                    {{ $subject['credits'] }} créditos
                                </span>
                            </div>

                            <h3 class="mt-4 flex-grow text-base font-semibold leading-snug text-[var(--color-foreground)]">
                                {{ $subject['name'] }}
                            </h3>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif
</x-page>

@if (!$semesters->isEmpty())
<script>
    (function () {
        const select = document.getElementById('semesterSelect');
        const buttons = document.querySelectorAll('.semester-btn');
        const panels = document.querySelectorAll('.semester-panel');

        function show(semester) {
            panels.forEach(panel => {
                panel.classList.toggle('hidden', panel.id !== 'semester-' + semester);
            });
            buttons.forEach(btn => {
                const active = btn.dataset.semester === String(semester);
                btn.classList.toggle('border-[var(--color-primary)]', active);
                btn.classList.toggle('bg-[var(--color-primary)]', active);
                btn.classList.toggle('text-[var(--color-on-primary)]', active);
                btn.classList.toggle('border-[var(--color-border)]', !active);
                btn.classList.toggle('bg-[var(--color-card)]', !active);
                btn.classList.toggle('text-[var(--color-foreground)]', !active);
            });
            if (select && select.value !== String(semester)) {
                select.value = String(semester);
            }
        }

        buttons.forEach(btn => btn.addEventListener('click', () => show(btn.dataset.semester)));
        if (select) select.addEventListener('change', () => show(select.value));
    })();
</script>
@endif
@endsection
