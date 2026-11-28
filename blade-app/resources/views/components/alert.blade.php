@props(['type' => 'error'])

@php
    $types = [
        'error'   => 'bg-[color-mix(in_srgb,var(--color-destructive)_10%,transparent)] text-[var(--color-destructive)] border border-[color-mix(in_srgb,var(--color-destructive)_25%,transparent)]',
        'success' => 'bg-[color-mix(in_srgb,var(--color-success)_10%,transparent)] text-[var(--color-success)] border border-[color-mix(in_srgb,var(--color-success)_25%,transparent)]',
        'warning' => 'bg-[color-mix(in_srgb,var(--color-warning)_10%,transparent)] text-[var(--color-warning)] border border-[color-mix(in_srgb,var(--color-warning)_25%,transparent)]',
        'info'    => 'bg-[var(--color-muted)] text-[var(--color-foreground)] border border-[var(--color-border)]',
    ];
    $cls = $types[$type] ?? $types['error'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl px-4 py-3 text-xs font-medium leading-relaxed ' . $cls, 'role' => 'alert']) }}>
    {{ $slot }}
</div>