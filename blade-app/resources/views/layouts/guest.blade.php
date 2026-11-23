<!DOCTYPE html>
<html lang="es" data-theme="dark" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Synapse Campus')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=EB+Garamond:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <meta name="color-scheme" content="light dark" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.toasts')
    <style>
    [data-theme="dark"] .login-brand-panel {
        background: linear-gradient(to bottom right, #1D1771, #4F46E5);
    }
</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-[var(--color-background)] text-[var(--color-foreground)] font-[family-name:var(--font-sans)] p-4">
    @yield('content')
</body>
</html>
