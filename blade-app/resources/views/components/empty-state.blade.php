@props(['title' => 'Sin resultados', 'description' => null, 'action' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 rounded-[var(--radius-lg)] border border-dashed border-[var(--color-border)] bg-[var(--color-muted)]/40 px-6 py-12 text-center']) }}>
    <div class="text-3xl opacity-60">∅</div>
    <div>
        <p class="text-sm font-semibold text-[var(--color-foreground)]">{{ $title }}</p>
        @if ($description)
            <p class="mt-1 text-xs text-[var(--color-muted-foreground)]">{{ $description }}</p>
        @endif
    </div>
    @if ($action)
        <div class="mt-2">{{ $action }}</div>
    @endif
</div>
