@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'href' => null])

@php
    $variants = [
        'primary' => 'bg-[var(--color-primary)] text-[var(--color-on-primary)] hover:bg-[var(--color-primary-hover)] shadow-[var(--shadow-sm)]',
        'accent'  => 'bg-[var(--color-accent)] text-[var(--color-on-accent)] hover:bg-[var(--color-primary-hover)] shadow-[var(--shadow-sm)]',
        'ghost'   => 'bg-[var(--color-card)] text-[var(--color-foreground)] border border-[var(--color-border)] hover:bg-[var(--color-muted)]',
        'danger'  => 'bg-[var(--color-destructive)] text-[var(--color-on-destructive)] hover:opacity-90 shadow-[var(--shadow-sm)]',
        'success' => 'bg-[var(--color-success)] text-white hover:opacity-90 shadow-[var(--shadow-sm)]',
    ];
    $sizes = [
        'sm' => 'h-8 px-3 text-xs',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-5 text-sm',
    ];
    $v = $variants[$variant] ?? $variants['primary'];
    $s = $sizes[$size] ?? $sizes['md'];
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @else type="{{ $type }}" @endif {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-all cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-[var(--color-background)] disabled:opacity-50 disabled:cursor-not-allowed ' . $v . ' ' . $s]) }}>
    {{ $slot }}
</{{ $tag }}>