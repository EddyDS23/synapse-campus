@props(['label' => '', 'value' => '', 'meta' => null, 'tone' => 'primary'])

@php
    $toneMap = [
        'primary' => 'text-[var(--color-primary)]',
        'accent'  => 'text-[var(--color-accent)]',
        'neutral' => 'text-[var(--color-foreground)]',
        'success' => 'text-[var(--color-success)]',
        'warning' => 'text-[var(--color-warning)]',
        'danger'  => 'text-[var(--color-destructive)]',
    ];
    $valueClass = $toneMap[$tone] ?? $toneMap['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)] transition-colors']) }}>
    <p class="text-[11px] font-semibold uppercase tracking-wider text-[var(--color-muted-foreground)]">{{ $label }}</p>
    <p class="mt-2.5 text-2xl sm:text-3xl font-bold tracking-tight leading-none {{ $valueClass }}">{{ $value }}</p>
    @if ($meta)
        <p class="mt-2 text-xs text-[var(--color-muted-foreground)]">{{ $meta }}</p>
    @endif
</div>
