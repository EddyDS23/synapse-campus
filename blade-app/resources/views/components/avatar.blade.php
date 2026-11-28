@props(['name' => '', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-14 w-14 text-lg',
    ];
    $s = $sizes[$size] ?? $sizes['md'];
    $initials = strtoupper(mb_substr(trim($name), 0, 2));
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full bg-[var(--color-primary)] font-bold text-[var(--color-on-primary)] shrink-0 tracking-wide ' . $s]) }}>
    {{ $slot->isEmpty() ? $initials : $slot }}
</div>