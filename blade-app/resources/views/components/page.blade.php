@props([])

<div {{ $attributes->merge(['class' => 'mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-6']) }}>
    {{ $slot }}
</div>
