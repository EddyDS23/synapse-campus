@props(['variant' => 'neutral'])

@php
    $variants = [
        'ok'       => 'bg-[color-mix(in_srgb,var(--color-success)_12%,transparent)] text-[var(--color-success)] border border-[color-mix(in_srgb,var(--color-success)_25%,transparent)]',
        'warn'     => 'bg-[color-mix(in_srgb,var(--color-warning)_12%,transparent)] text-[var(--color-warning)] border border-[color-mix(in_srgb,var(--color-warning)_25%,transparent)]',
        'danger'   => 'bg-[color-mix(in_srgb,var(--color-destructive)_12%,transparent)] text-[var(--color-destructive)] border border-[color-mix(in_srgb,var(--color-destructive)_25%,transparent)]',
        'accent'   => 'bg-[color-mix(in_srgb,var(--color-accent)_12%,transparent)] text-[var(--color-accent)] border border-[color-mix(in_srgb,var(--color-accent)_25%,transparent)]',
        'primary'  => 'bg-[color-mix(in_srgb,var(--color-primary)_12%,transparent)] text-[var(--color-primary)] border border-[color-mix(in_srgb,var(--color-primary)_25%,transparent)]',
        'neutral'  => 'bg-[var(--color-muted)] text-[var(--color-muted-foreground)] border border-[var(--color-border)]',
    ];
    $cls = $variants[$variant] ?? $variants['neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ' . $cls]) }}>
    {{ $slot }}
</span>
