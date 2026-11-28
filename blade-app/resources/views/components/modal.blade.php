@props(['title' => ''])

<div {{ $attributes->merge(['class' => 'fixed inset-0 z-50 flex items-center justify-center bg-black/55 backdrop-blur-[2px] p-4', 'role' => 'dialog', 'aria-modal' => 'true']) }}>
    <div class="w-full max-w-lg rounded-2xl border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)]">
        @if ($title)
            <h2 class="mb-4 text-base font-bold text-[var(--color-foreground)] tracking-tight">{{ $title }}</h2>
        @endif
        {{ $slot }}
    </div>
</div>