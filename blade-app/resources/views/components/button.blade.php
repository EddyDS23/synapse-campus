@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
    $variants = [
        'primary' => 'bg-[var(--color-primary)] text-[var(--color-on-primary)] shadow-sm hover:opacity-90',
        'accent'  => 'bg-[var(--color-accent)] text-[var(--color-on-accent)] shadow-sm hover:opacity-90',
        'ghost'   => 'bg-[var(--color-card)] text-[var(--color-foreground)] border border-[var(--color-border)] hover:bg-[var(--color-muted)]',
        'danger'  => 'bg-[var(--color-destructive)] text-[var(--color-on-destructive)] hover:opacity-90',
        'success' => 'bg-[var(--color-success)] text-white hover:opacity-90',
    ];
    $sizes = [
        'sm' => 'h-8 px-3 text-xs',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-5 text-base',
    ];
    $v = $variants[$variant] ?? $variants['primary'];
    $s = $sizes[$size] ?? $sizes['md'];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-[var(--radius-md)] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-background)] disabled:opacity-50 disabled:cursor-not-allowed ' . $v . ' ' . $s]) }}>
    {{ $slot }}
</button>
