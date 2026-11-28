@props(['title' => null, 'subtitle' => null, 'padding' => 'p-5', 'hover' => false])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-card-foreground)] shadow-[var(--shadow-sm)] transition-all ' . ($hover ? 'hover:shadow-[var(--shadow-md)] hover:-translate-y-0.5' : '') . ' ' . $padding]) }}>
    @if ($title || $subtitle || isset($header))
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                @if ($title)
                    <h3 class="text-sm font-bold text-[var(--color-foreground)] tracking-tight leading-snug">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-[var(--color-muted-foreground)]">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($header)
                <div class="shrink-0">{{ $header }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
    @isset($footer)
        <div class="mt-4 border-t border-[var(--color-border)] pt-4">{{ $footer }}</div>
    @endisset
</div>