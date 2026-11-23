@props([])

<div {{ $attributes->merge(['class' => 'mx-auto w-full max-w-7xl px-6 sm:px-8 lg:px-10 py-8']) }}>
    {{ $slot }}
</div>
