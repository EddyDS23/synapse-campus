@php
    $flash = [
        'success' => session('success'),
        'error'   => session('error') ?: ($errors->any() ? $errors->first() : null),
    ];
@endphp
<script>
    window.__FLASH__ = @json($flash);
</script>
