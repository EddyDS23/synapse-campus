@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'flex items-center gap-1.5 text-xs']) }} aria-label="Breadcrumb">
    @foreach ($items as $i => $item)
        @if ($i > 0)
            <svg class="h-3.5 w-3.5 text-[var(--color-muted-foreground)] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        @endif
        @if (!empty($item['url']))
            <a href="{{ $item['url'] }}" class="text-[var(--color-muted-foreground)] hover:text-[var(--color-primary)] font-medium transition-colors">{{ $item['label'] }}</a>
        @else
            <span class="font-semibold text-[var(--color-foreground)]">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>