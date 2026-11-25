@props(['name' => '', 'size' => 'md'])

@php
    $sizes = ['sm' => 'h-8 w-8 text-xs', 'md' => 'h-10 w-10 text-sm', 'lg' => 'h-14 w-14 text-lg'];
    $s = $sizes[$size] ?? $sizes['md'];
    $initials = strtoupper(mb_substr($name, 0, 2));
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-[var(--radius-md)] bg-[var(--color-primary)] font-semibold text-[var(--color-on-primary)] ' . $s]) }}>
    {{ $slot->isEmpty() ? $initials : $slot }}
</div>
