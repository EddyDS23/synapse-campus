@props([
    'variant' => 'dark', // 'dark' para navbar oscuro (texto blanco), 'light' para login/fondo claro (texto carbón)
    'showText' => true,
    'size' => 'md',
])

@php
    $iconSizes = [
        'sm' => 'w-7 h-7',
        'md' => 'w-9 h-9',
        'lg' => 'w-12 h-12',
    ];
    $iconClass = $iconSizes[$size] ?? $iconSizes['md'];
    $titleColor = $variant === 'dark' ? 'text-white' : 'text-stone-900';
    $subtitleColor = $variant === 'dark' ? 'text-rose-300' : 'text-[#881337]';
    $bookOuter = $variant === 'dark' ? '#e11d48' : '#881337';
    $bookInner = $variant === 'dark' ? '#fafaf9' : '#1c1917';
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 select-none']) }}>
    {{-- Isotipo: Libro abierto + red neuronal Synapse --}}
    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="{{ $iconClass }} shrink-0" aria-hidden="true">
        {{-- Páginas exteriores --}}
        <path d="M8 16C16 14 25 16 32 21C39 16 48 14 56 16V48C48 46 39 48 32 53C25 48 16 46 8 48V16Z" stroke="{{ $bookOuter }}" stroke-width="3.5" stroke-linejoin="round"/>
        {{-- Lomo central --}}
        <path d="M32 21V53" stroke="{{ $bookOuter }}" stroke-width="3" stroke-linecap="round"/>
        {{-- Conexiones de sinapsis --}}
        <path d="M19 26L25 36L32 31L42 25L46 34L39 40L32 31L22 39" stroke="#be123c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M19 26L32 31" stroke="{{ $bookInner }}" stroke-width="2" stroke-linecap="round"/>
        {{-- Nodos --}}
        <circle cx="19" cy="26" r="3.2" fill="{{ $bookInner }}"/>
        <circle cx="25" cy="36" r="3" fill="#be123c"/>
        <circle cx="22" cy="39" r="2.2" fill="{{ $bookInner }}"/>
        <circle cx="32" cy="31" r="3.8" fill="#e11d48"/>
        <circle cx="42" cy="25" r="3.2" fill="#be123c"/>
        <circle cx="46" cy="34" r="2.8" fill="#881337"/>
        <circle cx="39" cy="40" r="3" fill="{{ $bookInner }}"/>
    </svg>

    @if ($showText)
        <div class="flex flex-col leading-none">
            <span class="text-base sm:text-lg font-extrabold tracking-tight {{ $titleColor }}">
                SYNAPSE
            </span>
            <span class="text-[10px] font-bold tracking-[0.22em] uppercase mt-0.5 {{ $subtitleColor }}">
                CAMPUS
            </span>
        </div>
    @endif
</div>