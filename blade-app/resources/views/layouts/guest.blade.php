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
    :root {
        --color-primary: #1E3A5F; --color-on-primary: #FFFFFF; --color-secondary: #2563EB; --color-accent: #A16207; --color-on-accent: #FFFFFF;
        --color-background: #F8FAFC; --color-foreground: #0F172A; --color-card: #FFFFFF; --color-card-foreground: #0F172A; --color-muted: #E9EEF5; --color-muted-foreground: #475569; --color-border: #CBD5E1;
        --color-destructive: #DC2626; --color-success: #16A34A; --color-warning: #D97706;
        --radius-sm: 6px; --radius-md: 10px; --radius-lg: 14px; --radius-xl: 20px;
        --shadow-sm: 0 1px 2px rgba(15, 23, 42, .06), 0 1px 3px rgba(15, 23, 42, .08);
        --shadow-md: 0 4px 12px rgba(15, 23, 42, .08), 0 2px 4px rgba(15, 23, 42, .06);
        --shadow-lg: 0 12px 32px rgba(15, 23, 42, .12), 0 4px 8px rgba(15, 23, 42, .06);
        --font-sans: 'Atkinson Hyperlegible', ui-sans-serif, system-ui, sans-serif;
        --font-serif: 'EB Garamond', ui-serif, Georgia, serif;
        --font-mono: 'JetBrains Mono', ui-monospace, monospace;
    }
    [data-theme="dark"] {
        --color-primary: #93C5FD; --color-on-primary: #0F172A; --color-secondary: #60A5FA; --color-accent: #FBBF24; --color-on-accent: #1E293B;
        --color-background: #0B1120; --color-foreground: #E2E8F0; --color-card: #111827; --color-card-foreground: #E2E8F0; --color-muted: #1E293B; --color-muted-foreground: #94A3B8; --color-border: #1F2937;
        --color-destructive: #F87171; --color-success: #4ADE80; --color-warning: #FBBF24;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, .4), 0 1px 3px rgba(0, 0, 0, .3);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, .45), 0 2px 4px rgba(0, 0, 0, .3);
        --shadow-lg: 0 12px 32px rgba(0, 0, 0, .55), 0 4px 8px rgba(0, 0, 0, .35);
    }
</style>
</head>
<body class="min-h-screen flex items-center justify-center bg-[var(--color-background)] text-[var(--color-foreground)] font-[family-name:var(--font-sans)] p-4">
    @yield('content')
</body>
</html>
