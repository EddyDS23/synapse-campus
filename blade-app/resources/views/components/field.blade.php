@props(['label' => null, 'error' => null, 'hint' => null])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label class="block text-xs font-medium text-[var(--color-foreground)]">{{ $label }}</label>
    @endif
    {{ $slot }}
    @if ($hint && !$error)
        <p class="text-xs text-[var(--color-muted-foreground)]">{{ $hint }}</p>
    @endif
    @if ($error)
        <p class="text-xs text-[var(--color-destructive)]" role="alert">{{ $error }}</p>
    @endif
</div>
