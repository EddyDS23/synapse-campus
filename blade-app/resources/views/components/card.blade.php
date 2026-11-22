@props(['title' => null, 'subtitle' => null, 'padding' => 'p-5', 'hover' => false])

<div {{ $attributes->merge(['class' => 'rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-[var(--color-card)] text-[var(--color-card-foreground)] shadow-[var(--shadow-sm)] transition ' . ($hover ? 'hover:shadow-[var(--shadow-md)] hover:-translate-y-0.5' : '') . ' ' . $padding]) }}>
    @if ($title || $subtitle || isset($header))
        <div class="mb-4 flex items-start justify-between gap-3">
            <div>
                @if ($title)
                    <h3 class="text-base font-semibold font-[family-name:var(--font-serif)] leading-tight">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="mt-1 text-xs text-[var(--color-muted-foreground)]">{{ $subtitle }}</p>
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
