<!DOCTYPE html>
<html lang="es" data-theme="light" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Synapse Campus')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=EB+Garamond:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
    <meta name="color-scheme" content="light dark" />
    <script>
        (function() {
            const saved = localStorage.getItem('sc-theme');
            if (saved) {
                document.documentElement.setAttribute('data-theme', saved);
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Escala equivalente a ~120-125% en escritorio para Login y 2FA */
        @media (min-width: 1024px) {
            html {
                font-size: 19px;
            }
        }
    </style>
    @include('partials.toasts')
</head>
<body class="min-h-screen bg-[var(--color-background)] text-[var(--color-foreground)] font-[family-name:var(--font-sans)] antialiased selection:bg-[var(--color-primary)] selection:text-white transition-colors duration-200">
    @yield('content')

    <script>
        function updateGuestThemeUI(theme) {
            const isDark = theme === 'dark';
            const sunSvg = '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>';
            const moonSvg = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';

            const iconDesktop = document.getElementById('theme-icon-desktop');
            const iconMobile = document.getElementById('theme-icon-mobile');
            const label = document.getElementById('theme-label');

            if (iconDesktop) iconDesktop.innerHTML = isDark ? sunSvg : moonSvg;
            if (iconMobile) iconMobile.innerHTML = isDark ? sunSvg : moonSvg;
            if (label) label.textContent = isDark ? 'Modo Claro' : 'Modo Oscuro';
        }

        function toggleTheme() {
            const html = document.documentElement;
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('sc-theme', next);
            updateGuestThemeUI(next);
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateGuestThemeUI(document.documentElement.getAttribute('data-theme') || 'light');
        });
    </script>
</body>
</html>