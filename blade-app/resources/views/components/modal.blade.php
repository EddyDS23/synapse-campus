@props(['title' => ''])

<div {{ $attributes->merge(['class' => 'fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4', 'role' => 'dialog', 'aria-modal' => 'true']) }}>
    <div class="w-full max-w-lg rounded-[var(--radius-xl)] border border-[var(--color-border)] bg-[var(--color-card)] p-6 shadow-[var(--shadow-lg)]">
        @if ($title)
            <h2 class="mb-4 text-lg font-semibold font-[family-name:var(--font-serif)]">{{ $title }}</h2>
        @endif
        {{ $slot }}
    </div>
</div>
