@props(['label' => '', 'value' => '', 'meta' => null, 'tone' => 'primary'])

@php
    $toneMap = [
        'primary' => 'text-[var(--color-primary)]',
        'accent'  => 'text-[var(--color-accent)]',
        'success' => 'text-[var(--color-success)]',
        'warning' => 'text-[var(--color-warning)]',
        'danger'  => 'text-[var(--color-destructive)]',
    ];
    $valueClass = $toneMap[$tone] ?? $toneMap['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-[var(--color-card)] p-5 shadow-[var(--shadow-sm)]']) }}>
    <p class="text-[11px] font-medium uppercase tracking-wider text-[var(--color-muted-foreground)]">{{ $label }}</p>
    <p class="mt-2 text-3xl font-bold font-[family-name:var(--font-serif)] leading-none {{ $valueClass }}">{{ $value }}</p>
    @if ($meta)
        <p class="mt-2 text-xs text-[var(--color-muted-foreground)]">{{ $meta }}</p>
    @endif
</div>
