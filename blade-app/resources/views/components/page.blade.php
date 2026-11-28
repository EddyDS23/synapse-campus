@props([])

<div {{ $attributes->merge(['class' => 'mx-auto w-full max-w-7xl space-y-6']) }}>
    {{ $slot }}
</div>