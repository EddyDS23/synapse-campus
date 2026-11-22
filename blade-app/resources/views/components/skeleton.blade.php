@props(['lines' => 3])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-2']) }} aria-hidden="true">
    @for ($i = 0; $i < $lines; $i++)
        <div class="h-3 rounded bg-[var(--color-muted)]" style="width: {{ $i === $lines - 1 ? '60%' : '100%' }}"></div>
    @endfor
</div>
