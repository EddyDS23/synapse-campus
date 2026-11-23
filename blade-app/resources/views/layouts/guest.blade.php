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
        --color-primary: #DC2626; --color-on-primary: #FFFFFF; --color-secondary: #F97316; --color-accent: #F59E0B; --color-on-accent: #1F2937;
        --color-background: #FEF2F2; --color-foreground: #1F2937; --color-card: #FFFFFF; --color-card-foreground: #1F2937; --color-muted: #FEE2E2; --color-muted-foreground: #6B7280; --color-border: #FECACA;
        --color-destructive: #991B1B; --color-success: #16A34A; --color-warning: #D97706;
        --radius-sm: 6px; --radius-md: 10px; --radius-lg: 14px; --radius-xl: 20px;
        --shadow-sm: 0 1px 2px rgba(15, 23, 42, .06), 0 1px 3px rgba(15, 23, 42, .08);
        --shadow-md: 0 4px 12px rgba(15, 23, 42, .08), 0 2px 4px rgba(15, 23, 42, .06);
        --shadow-lg: 0 12px 32px rgba(15, 23, 42, .12), 0 4px 8px rgba(15, 23, 42, .06);
        --font-sans: 'Atkinson Hyperlegible', ui-sans-serif, system-ui, sans-serif;
        --font-serif: 'EB Garamond', ui-serif, Georgia, serif;
        --font-mono: 'JetBrains Mono', ui-monospace, monospace;
    }
    [data-theme="dark"] {
        --color-primary: #F87171; --color-on-primary: #1F2937; --color-secondary: #FB923C; --color-accent: #FBBF24; --color-on-accent: #1F2937;
        --color-background: #0B1120; --color-foreground: #E2E8F0; --color-card: #111827; --color-card-foreground: #E2E8F0; --color-muted: #1E293B; --color-muted-foreground: #94A3B8; --color-border: #1F2937;
        --color-destructive: #FCA5A5; --color-success: #4ADE80; --color-warning: #FBBF24;
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
