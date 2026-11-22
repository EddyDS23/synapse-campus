@props(['page' => 1, 'lastPage' => 1, 'baseUrl' => ''])

<nav {{ $attributes->merge(['class' => 'flex items-center justify-between gap-4 border-t border-[var(--color-border)] px-4 py-3 text-sm']) }} aria-label="Paginación">
    <span class="text-[var(--color-muted-foreground)]">Página {{ $page }} de {{ $lastPage }}</span>
    <div class="flex gap-1">
        @if ($page > 1)
            <a href="{{ $baseUrl }}?page={{ $page - 1 }}" class="rounded-[var(--radius-sm)] border border-[var(--color-border)] bg-[var(--color-card)] px-3 py-1.5 hover:bg-[var(--color-muted)]">Anterior</a>
        @endif
        @if ($page < $lastPage)
            <a href="{{ $baseUrl }}?page={{ $page + 1 }}" class="rounded-[var(--radius-sm)] border border-[var(--color-border)] bg-[var(--color-card)] px-3 py-1.5 hover:bg-[var(--color-muted)]">Siguiente</a>
        @endif
    </div>
</nav>
