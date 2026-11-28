@props(['page' => 1, 'lastPage' => 1, 'baseUrl' => ''])

<nav {{ $attributes->merge(['class' => 'flex items-center justify-between gap-4 border-t border-[var(--color-border)] px-5 py-3.5 text-xs']) }} aria-label="Paginación">
    <span class="font-medium text-[var(--color-muted-foreground)]">
        Página <strong class="font-semibold text-[var(--color-foreground)]">{{ $page }}</strong> de <strong class="font-semibold text-[var(--color-foreground)]">{{ $lastPage }}</strong>
    </span>
    <div class="flex items-center gap-2">
        @if ($page > 1)
            <a href="{{ $baseUrl }}?page={{ $page - 1 }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-3 py-1.5 font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Anterior
            </a>
        @endif
        @if ($page < $lastPage)
            <a href="{{ $baseUrl }}?page={{ $page + 1 }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-card)] px-3 py-1.5 font-semibold text-[var(--color-foreground)] hover:bg-[var(--color-muted)] transition-colors">
                Siguiente
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
        @endif
    </div>
</nav>