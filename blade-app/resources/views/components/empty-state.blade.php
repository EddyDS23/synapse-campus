@props(['title' => 'Sin resultados', 'description' => null, 'action' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-[var(--color-border)] bg-[var(--color-muted)]/40 px-6 py-12 text-center']) }}>
    <div class="w-11 h-11 rounded-2xl bg-[var(--color-muted)] border border-[var(--color-border)] text-[var(--color-muted-foreground)] flex items-center justify-center">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
            <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
        </svg>
    </div>
    <div class="max-w-sm">
        <p class="text-sm font-semibold text-[var(--color-foreground)]">{{ $title }}</p>
        @if ($description)
            <p class="mt-1 text-xs text-[var(--color-muted-foreground)] leading-relaxed">{{ $description }}</p>
        @endif
    </div>
    @if ($action)
        <div class="mt-2">{{ $action }}</div>
    @endif
</div>