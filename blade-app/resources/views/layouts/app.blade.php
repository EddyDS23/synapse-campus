<!DOCTYPE html>
<html lang="es" data-theme="light" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', 'Synapse Campus')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=EB+Garamond:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet" />
    <meta name="color-scheme" content="light dark" />
    <script>
        (function() {
            const saved = localStorage.getItem('sc-theme');
            if (saved) {
                document.documentElement.setAttribute('data-theme', saved);
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media (min-width: 1024px) {
            html {
                font-size: 19px;
            }
        }

        :root {
            --sidebar-w: 240px;
            --topbar-h: 60px;
            --t: 160ms ease;
            --mono: var(--font-mono);
            --sans: var(--font-sans);
            --serif: var(--font-serif);

            /* Mapped to design tokens (60-30-10) */
            --bg: var(--color-background);
            --surface: var(--color-card);
            --elevated: var(--color-muted);
            --hover: var(--color-muted);
            --border: var(--color-border);
            --border-mid: var(--color-border);
            --text: var(--color-foreground);
            --text-sub: var(--color-muted-foreground);
            --text-muted: var(--color-muted-foreground);
            --accent: var(--color-primary);
            --accent-dim: color-mix(in srgb, var(--color-primary) 12%, transparent);
            --accent-glow: color-mix(in srgb, var(--color-primary) 22%, transparent);
            --ok: var(--color-success);
            --ok-dim: color-mix(in srgb, var(--color-success) 11%, transparent);
            --warn: var(--color-warning);
            --warn-dim: color-mix(in srgb, var(--color-warning) 11%, transparent);
            --danger: var(--color-destructive);
            --danger-dim: color-mix(in srgb, var(--color-destructive) 11%, transparent);
            --c-auth: var(--color-primary);
            --c-student: var(--color-secondary);
            --c-library: var(--color-success);
            --c-support: var(--color-warning);
            --c-audit: var(--color-muted-foreground);
            --shadow: var(--shadow-md);
            --shadow-sm: var(--shadow-sm);
        }

        @layer base {
            *,
            *::before,
            *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
        }

        html,
        body {
            height: 100%;
            font-family: var(--sans);
            font-size: 14px;
            line-height: 1.5;
            background: var(--bg);
            color: var(--text);
            transition: background var(--t), color var(--t);
            -webkit-font-smoothing: antialiased;
        }

        @layer base {
            a {
                text-decoration: none;
                color: inherit;
            }
        }

        .app {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ── SIDEBAR (30% Carbón) ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--color-chrome-bg);
            border-right: 1px solid var(--color-chrome-border);
            color: var(--color-chrome-fg);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow: hidden;
            transition: background var(--t), border-color var(--t);
            position: relative;
            z-index: 100;
        }

        .sb-brand {
            height: var(--topbar-h);
            padding: 0 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--color-chrome-border);
            flex-shrink: 0;
        }

        .sb-brand-badge {
            width: 30px;
            height: 30px;
            border-radius: var(--radius-sm);
            background: var(--color-primary);
            color: var(--color-on-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sb-brand-badge svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .sb-wordmark {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.4px;
            white-space: nowrap;
            color: var(--color-chrome-fg);
        }

        .sb-wordmark span {
            color: var(--color-chrome-muted);
            font-weight: 400;
        }

        .sb-nav {
            flex: 1;
            overflow-y: auto;
            padding: 12px 0;
            scrollbar-width: none;
        }

        .sb-nav::-webkit-scrollbar {
            display: none;
        }

        .sb-group {
            padding: 0 10px;
            margin-bottom: 2px;
        }

        .sb-group-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--color-chrome-muted);
            padding: 8px 10px 4px;
        }

        .sb-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            color: var(--color-chrome-muted);
            font-size: 13px;
            font-weight: 500;
            transition: background var(--t), color var(--t);
            position: relative;
            white-space: nowrap;
        }

        .sb-item:hover {
            background: var(--color-chrome-elevated);
            color: var(--color-chrome-fg);
        }

        /* 10% Acento Carmesí en ítem activo */
        .sb-item.active {
            background: var(--color-primary);
            color: var(--color-on-primary);
            font-weight: 600;
        }

        .sb-icon {
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sb-icon svg {
            width: 15px;
            height: 15px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .sb-badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 10px;
            line-height: 16px;
            color: #fff;
        }

        .sb-badge.red {
            background: var(--danger);
        }

        .sb-badge.yellow {
            background: var(--warn);
        }

        .sb-badge.grey {
            background: var(--color-chrome-elevated);
            color: var(--color-chrome-muted);
            border: 1px solid var(--color-chrome-border);
        }

        .sb-divider {
            height: 1px;
            background: var(--color-chrome-border);
            margin: 6px 10px;
        }

        /* Ecosystem */
        .sb-ecosystem {
            padding: 8px 14px;
            border-top: 1px solid var(--color-chrome-border);
            flex-shrink: 0;
        }

        .sb-eco-row {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 2px 0;
            cursor: default;
        }

        .eco-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .eco-dot.online {
            background: #4ADE80;
            animation: ecoPulse 2.5s ease-in-out infinite;
        }

        .eco-dot.degraded {
            background: #FBBF24;
        }

        .eco-dot.offline {
            background: #F87171;
        }

        @keyframes ecoPulse {
            0%, 100% { opacity: 1 }
            50% { opacity: 0.35 }
        }

        .eco-name {
            font-family: var(--mono);
            font-size: 9px;
            color: var(--color-chrome-muted);
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* User footer en Sidebar */
        .sb-user {
            padding: 10px 12px;
            border-top: 1px solid var(--color-chrome-border);
            display: flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            transition: background var(--t);
            flex-shrink: 0;
            position: relative;
        }

        .sb-user:hover {
            background: var(--color-chrome-elevated);
        }

        .sb-avatar {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            background: var(--color-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--color-on-primary);
            flex-shrink: 0;
            letter-spacing: 0.3px;
        }

        .sb-user-info {
            flex: 1;
            min-width: 0;
        }

        .sb-user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-chrome-fg);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sb-user-role {
            font-size: 11px;
            color: var(--color-chrome-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sb-user-menu {
            color: var(--color-chrome-muted);
            display: flex;
            align-items: center;
        }

        .sb-user-menu svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            bottom: calc(100% + 6px);
            left: 10px;
            right: 10px;
            background: var(--color-card);
            color: var(--color-foreground);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            z-index: 200;
        }

        .user-dropdown.open {
            display: block;
            animation: fadeUp 150ms ease;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(6px) }
            to { opacity: 1; transform: translateY(0) }
        }

        .ud-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 14px;
            font-size: 13px;
            color: var(--color-muted-foreground);
            cursor: pointer;
            transition: background var(--t), color var(--t);
        }

        .ud-item:hover {
            background: var(--color-muted);
            color: var(--color-foreground);
        }

        .ud-item svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        .ud-divider {
            height: 1px;
            background: var(--color-border);
        }

        .ud-item.danger {
            color: var(--danger);
        }

        .ud-item.danger:hover {
            background: var(--danger-dim);
        }

        .ud-email {
            padding: 10px 14px;
            font-family: var(--mono);
            font-size: 10px;
            color: var(--color-muted-foreground);
            border-bottom: 1px solid var(--color-border);
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── MAIN & TOPBAR (30% Carbón) ── */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
            background: var(--color-background);
            color: var(--color-foreground);
        }

        .topbar {
            height: var(--topbar-h);
            background: var(--color-chrome-bg);
            border-bottom: 1px solid var(--color-chrome-border);
            color: var(--color-chrome-fg);
            padding: 0 32px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            transition: background var(--t), border-color var(--t);
        }

        .hamburger {
            display: none;
            width: 34px;
            height: 34px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--color-chrome-border);
            background: var(--color-chrome-elevated);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--color-chrome-fg);
            transition: all var(--t);
            flex-shrink: 0;
        }

        .hamburger:hover {
            background: var(--color-primary);
            border-color: var(--color-primary);
        }

        .hamburger svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--color-chrome-muted);
        }

        .breadcrumb svg {
            width: 12px;
            height: 12px;
            stroke: var(--color-chrome-muted);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        .breadcrumb-current {
            color: var(--color-chrome-fg);
            font-weight: 600;
        }

        .topbar-spacer {
            flex: 1;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--color-chrome-border);
            background: var(--color-chrome-elevated);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--color-chrome-muted);
            transition: all var(--t);
            position: relative;
            flex-shrink: 0;
        }

        .icon-btn svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .icon-btn:hover {
            color: var(--color-chrome-fg);
            border-color: var(--color-primary);
        }

        .notif-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--color-primary);
            border: 1.5px solid var(--color-chrome-elevated);
        }

        /* ── CONTENT (60% Neutro) ── */
        .content {
            flex: 1;
            overflow-y: auto;
            padding: 32px 40px;
            scrollbar-width: thin;
            scrollbar-color: var(--border) transparent;
        }

        .content::-webkit-scrollbar {
            width: 4px;
        }

        .content::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        .sb-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .55);
            z-index: 99;
        }

        .sb-overlay.visible {
            display: block;
        }

        /* ── COMPONENTES GLOBALES ── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 28px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.4px;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--text-sub);
            margin-top: 3px;
        }

        .page-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-shrink: 0;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            transition: background var(--t), border-color var(--t);
        }

        .card-header {
            padding: 20px 24px 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
        }

        .card-subtitle {
            font-size: 12px;
            color: var(--text-sub);
            margin-top: 2px;
        }

        .card-body {
            padding: 20px 24px;
        }

        .card-link {
            font-size: 12px;
            color: var(--accent);
            font-weight: 600;
        }

        .card-link:hover {
            opacity: .75;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 18px 20px;
            transition: background var(--t), border-color var(--t);
        }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--text-sub);
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -.6px;
            line-height: 1;
            margin-bottom: 5px;
        }

        .stat-meta {
            font-size: 12px;
            color: var(--text-sub);
        }

        .stat-meta .up { color: var(--ok); }
        .stat-meta .down { color: var(--danger); }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        .grid-2-1 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        .mb-0 { margin-bottom: 0; }
        .mb-18 { margin-bottom: 18px; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 0 16px;
            height: 36px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: var(--sans);
            transition: all var(--t);
            white-space: nowrap;
        }

        .btn svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--color-primary-hover);
        }

        .btn-ghost {
            background: var(--elevated);
            color: var(--text-sub);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            border-color: var(--border-mid);
            color: var(--text);
        }

        .btn-danger {
            background: var(--danger-dim);
            color: var(--danger);
            border: 1px solid color-mix(in srgb, var(--danger) 25%, transparent);
        }

        .btn-danger:hover {
            background: color-mix(in srgb, var(--danger) 20%, transparent);
        }

        .btn-sm {
            height: 30px;
            padding: 0 12px;
            font-size: 12px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .2px;
        }

        .badge::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
            flex-shrink: 0;
        }

        .badge-ok { background: var(--ok-dim); color: var(--ok); }
        .badge-warn { background: var(--warn-dim); color: var(--warn); }
        .badge-danger { background: var(--danger-dim); color: var(--danger); }
        .badge-neutral {
            background: var(--elevated);
            color: var(--text-sub);
            border: 1px solid var(--border);
        }
        .badge-accent { background: var(--accent-dim); color: var(--accent); }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: var(--radius-sm);
            font-size: 11px;
            font-weight: 600;
        }

        .table-wrap { overflow-x: auto; }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .9px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0 16px 10px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .table td {
            padding: 11px 16px;
            font-size: 13px;
            border-bottom: 1px solid var(--border);
            color: var(--text-sub);
            vertical-align: middle;
        }

        .table tr:last-child td { border-bottom: none; }
        .table tbody tr:hover td { background: var(--hover); }

        .td-primary { color: var(--text); font-weight: 500; }
        .td-mono { font-family: var(--mono); font-size: 11px; color: var(--text-muted); }

        .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
            border-top: 1px solid var(--border);
        }

        .page-btn {
            width: 30px;
            height: 30px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            background: var(--elevated);
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-sub);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--t);
            font-family: var(--sans);
        }

        .page-btn:hover { border-color: var(--border-mid); color: var(--text); }
        .page-btn.active { background: var(--accent); border-color: var(--accent); color: #fff; }
        .pag-spacer { flex: 1; }
        .pag-info { font-size: 12px; color: var(--text-sub); }

        .search-wrap { position: relative; }

        .search-wrap input {
            width: 100%;
            height: 34px;
            background: var(--elevated);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 0 12px 0 34px;
            font-family: var(--sans);
            font-size: 13px;
            color: var(--text);
            outline: none;
            transition: border-color var(--t);
        }

        .search-wrap input::placeholder { color: var(--text-muted); }
        .search-wrap input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-glow); }

        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .search-icon svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .divider { height: 1px; background: var(--border); }
        .mono { font-family: var(--mono); font-size: 11px; }

        .tst, .pri {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: var(--radius-sm);
        }

        .tst-open { background: var(--accent-dim); color: var(--accent); }
        .tst-inprogress { background: var(--warn-dim); color: var(--warn); }
        .tst-resolved { background: var(--ok-dim); color: var(--ok); }
        .tst-closed { background: var(--elevated); color: var(--text-sub); border: 1px solid var(--border); }
        .tst-onhold { background: var(--danger-dim); color: var(--danger); }

        .pri-urgent { background: var(--danger-dim); color: var(--danger); }
        .pri-high { background: var(--warn-dim); color: var(--warn); }
        .pri-medium { background: var(--accent-dim); color: var(--accent); }
        .pri-low { background: var(--ok-dim); color: var(--ok); }

        .alert {
            padding: 10px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-error {
            background: var(--danger-dim);
            color: var(--danger);
            border: 1px solid color-mix(in srgb, var(--danger) 20%, transparent);
        }

        .alert-success {
            background: var(--ok-dim);
            color: var(--ok);
            border: 1px solid color-mix(in srgb, var(--ok) 20%, transparent);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1280px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-2-1 { grid-template-columns: 1fr; }
        }

        @media (max-width: 1024px) {
            :root { --sidebar-w: 200px; }
            .grid-2 { grid-template-columns: 1fr; }
            .content { padding: 20px; }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                bottom: 0;
                z-index: 300;
                box-shadow: var(--shadow);
                transition: left 220ms ease;
            }
            .sidebar.open { left: 0; }
            .hamburger { display: flex; }
            .main { width: 100%; }
            .grid-3 { grid-template-columns: 1fr 1fr; }
            .content { padding: 16px; }
            .topbar { padding: 0 16px; }
        }

        @media (max-width: 480px) {
            .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr; }
            .content { padding: 12px; }
        }

        @yield('styles')
        
    </style>
    @include('partials.toasts')
    <style>
    @media (min-width: 768px) {
        html {
            font-size: 19px !important;
        }
    }
</style>
</head>

<body>
    <div class="app">

        <div class="sb-overlay" id="sbOverlay" onclick="closeSidebar()"></div>

        {{-- ══════════════════ SIDEBAR ══════════════════ --}}
        <aside class="sidebar" id="sidebar">

            <div class="sb-brand">
                <span class="sb-brand-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </span>
                <a href="{{ route('dashboard') }}" class="sb-wordmark">Synapse<span>Campus</span></a>
            </div>

            <nav class="sb-nav">

                {{-- ── Dashboard (todos) ── --}}
                <div class="sb-group">
                    <a href="{{ route('dashboard') }}" class="sb-item {{ request()->is('dashboard') ? 'active' : '' }}">
                        <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="7" rx="1" />
                                <rect x="14" y="3" width="7" height="7" rx="1" />
                                <rect x="3" y="14" width="7" height="7" rx="1" />
                                <rect x="14" y="14" width="7" height="7" rx="1" />
                            </svg></span>
                        Dashboard
                    </a>
                </div>

                {{-- ── ESTUDIANTE ── --}}
                @if (RoleHelper::hasRole('student'))
                    <div class="sb-divider"></div>
                    <div class="sb-group">
                        <div class="sb-group-label">Académico</div>

                        <a href="{{ route('student.profile') }}"
                            class="sb-item {{ request()->is('profile') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg></span>
                            Mi Perfil
                        </a>
                        <a href="{{ route('student.schedule') }}"
                            class="sb-item {{ request()->is('schedule') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg></span>
                            Mi Horario
                        </a>
                        <a href="{{ route('student.subjects') }}"
                            class="sb-item {{ request()->is('subjects') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z" />
                                    <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z" />
                                </svg></span>
                            Mis Materias
                        </a>
                    </div>

                    <div class="sb-divider"></div>
                    <div class="sb-group">
                        <div class="sb-group-label">Biblioteca</div>

                        <a href="{{ route('library.books') }}"
                            class="sb-item {{ request()->is('library/books*') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="M4 19.5A2.5 2.5 0 016.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
                                </svg></span>
                            Catálogo
                        </a>
                        <a href="{{ route('library.loans') }}"
                            class="sb-item {{ request()->is('library/loans') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <polyline points="9 11 12 14 22 4" />
                                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                                </svg></span>
                            Mis Préstamos
                        </a>
                        <a href="{{ route('library.fines') }}"
                            class="sb-item {{ request()->is('library/fines') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <line x1="12" y1="1" x2="12" y2="23" />
                                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                                </svg></span>
                            Mis Multas
                        </a>
                    </div>

                    <div class="sb-divider"></div>
                    <div class="sb-group">
                        <div class="sb-group-label">Soporte</div>

                        <a href="{{ route('support.my-tickets') }}"
                            class="sb-item {{ request()->is('support/tickets') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                                </svg></span>
                            Mis Tickets
                        </a>
                        <a href="{{ route('support.create') }}"
                            class="sb-item {{ request()->is('support/tickets/new') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg></span>
                            Nuevo Ticket
                        </a>
                    </div>
                @endif

                {{-- ── BIBLIOTECARIO ── --}}
                @if (RoleHelper::isLibrarian())
                    <div class="sb-divider"></div>
                    <div class="sb-group">
                        <div class="sb-group-label">Biblioteca</div>

                        <a href="{{ route('library.inventory') }}"
                            class="sb-item {{ request()->is('library/inventory*') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <line x1="8" y1="6" x2="21" y2="6" />
                                    <line x1="8" y1="12" x2="21" y2="12" />
                                    <line x1="8" y1="18" x2="21" y2="18" />
                                    <line x1="3" y1="6" x2="3.01" y2="6" />
                                    <line x1="3" y1="12" x2="3.01" y2="12" />
                                    <line x1="3" y1="18" x2="3.01" y2="18" />
                                </svg></span>
                            Inventario
                        </a>
                        <a href="{{ route('library.books') }}"
                            class="sb-item {{ request()->is('library/books*') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="M4 19.5A2.5 2.5 0 016.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
                                </svg></span>
                            Ver Catálogo
                        </a>
                    </div>
                @endif

                {{-- ── AGENTE DE SOPORTE ── --}}
                @if (RoleHelper::isAgent())
                    <div class="sb-divider"></div>
                    <div class="sb-group">
                        <div class="sb-group-label">Mesa de Ayuda</div>

                        <a href="{{ route('support.all-tickets') }}"
                            class="sb-item {{ request()->is('support/all-tickets*') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="M15 5H9a2 2 0 00-2 2v14l5-3 5 3V7a2 2 0 00-2-2z" />
                                </svg></span>
                            Todos los Tickets
                            @isset($pendingTickets)
                                @if ($pendingTickets > 0)
                                    <span class="sb-badge red">{{ $pendingTickets }}</span>
                                @endif
                            @endisset
                        </a>
                    </div>
                @endif

                {{-- ── ADMIN ── --}}
                @if (RoleHelper::hasAnyRole(['security_admin', 'super_admin', 'academic_admin']))
                    <div class="sb-divider"></div>
                    <div class="sb-group">
                        <div class="sb-group-label">Administración</div>

                        @if (RoleHelper::hasAnyRole(['security_admin', 'super_admin']))
                        <a href="{{ route('admin.users') }}"
                            class="sb-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                    <path d="M16 3.13a4 4 0 010 7.75" />
                                </svg></span>
                            Usuarios y Roles
                        </a>
                        @endif
                        <a href="{{ route('admin.audit') }}"
                            class="sb-item {{ request()->is('admin/audit*') ? 'active' : '' }}">
                            <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                </svg></span>
                            Auditoría
                        </a>

                        @if (RoleHelper::hasAnyRole(['security_admin', 'super_admin']))
                            <a href="{{ route('admin.security-status') }}"
                                class="sb-item {{ request()->is('admin/security-status*') ? 'active' : '' }}">
                                <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    </svg></span>
                                Estado de Seguridad
                            </a>
                        @endif
                    </div>
                @endif

                {{-- ── SEGURIDAD (todos los roles) ── --}}
                <div class="sb-divider"></div>
                <div class="sb-group">
                    <div class="sb-group-label">Mi cuenta</div>

                    <a href="{{ route('security.index') }}"
                        class="sb-item {{ request()->is('security*') ? 'active' : '' }}">
                        <span class="sb-icon"><svg aria-hidden="true" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0110 0v4" />
                            </svg></span>
                        Seguridad
                    </a>
                </div>

            </nav>

            {{-- Estado del ecosistema (solo admins) --}}
            @if (RoleHelper::hasAnyRole(['security_admin', 'super_admin', 'academic_admin']))
            <div class="sb-ecosystem">
                @foreach ($ecosystemHealth as $service => $status)
                    <div class="sb-eco-row" title="{{ $status }}">
                        <span class="eco-name">{{ $service }}</span>
                        <div class="eco-dot {{ $status }}"></div>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Usuario con avatar circular de inicial + dropdown --}}
            <div class="sb-user" onclick="toggleUserMenu()">
                <div class="sb-avatar">
                    {{ strtoupper(substr(session('email', 'U'), 0, 1)) }}
                </div>
                <div class="sb-user-info">
                    <div class="sb-user-name">{{ session('name', 'Usuario') }}</div>
                    <div class="sb-user-role">{{ implode(', ', session('roles', [])) }}</div>
                </div>
                <div class="sb-user-menu">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <circle cx="12" cy="5" r="1" />
                        <circle cx="12" cy="12" r="1" />
                        <circle cx="12" cy="19" r="1" />
                    </svg>
                </div>

                <div class="user-dropdown" id="userDropdown">
                    <div class="ud-email">{{ session('email', '') }}</div>
                    <a href="{{ route('student.profile') }}" class="ud-item">
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        Mi Perfil
                    </a>
                    <a href="{{ route('security.index') }}" class="ud-item">
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0110 0v4" />
                        </svg>
                        Seguridad
                    </a>
                    <div class="ud-divider"></div>
                    <form action="{{ route('logout') }}" method="post" id="logoutForm">
                        @csrf
                        <div class="ud-item danger" onclick="document.getElementById('logoutForm').submit()">
                            <svg aria-hidden="true" viewBox="0 0 24 24">
                                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            Cerrar sesión
                        </div>
                    </form>
                </div>
            </div>

        </aside>

        {{-- ══════════════════ MAIN ══════════════════ --}}
        <div class="main">

            <header class="topbar">
                <button class="hamburger" id="hamburger" onclick="openSidebar()" type="button" aria-label="Abrir menú">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>

                <div class="breadcrumb">
                    @hasSection('breadcrumb-parent')
                        @yield('breadcrumb-parent')
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    @endif
                    <span class="breadcrumb-current">@yield('title', 'Dashboard')</span>
                </div>

                <div class="topbar-spacer"></div>

                <div class="topbar-actions">
                    <button class="icon-btn" title="Notificaciones" aria-label="Notificaciones">
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 01-3.46 0" />
                        </svg>
                        <span class="notif-dot"></span>
                    </button>

                    <button class="icon-btn" onclick="toggleTheme()" id="themeBtn" type="button" title="Cambiar tema" aria-label="Cambiar tema">
                        <svg id="themeIcon" viewBox="0 0 24 24">
                            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                        </svg>
                    </button>
                </div>
            </header>

            <main class="content">
                @yield('content')
            </main>

        </div>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            const next = isDark ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('sc-theme', next);
            updateThemeBtn(next);
        }

        function updateThemeBtn(theme) {
            const icon = document.getElementById('themeIcon');
            const btn = document.getElementById('themeBtn');
            if (theme === 'light') {
                icon.innerHTML = '<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>';
                btn.setAttribute('title', 'Cambiar a modo oscuro');
                btn.setAttribute('aria-label', 'Cambiar a modo oscuro');
            } else {
                icon.innerHTML =
                    '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>';
                btn.setAttribute('title', 'Cambiar a modo claro');
                btn.setAttribute('aria-label', 'Cambiar a modo claro');
            }
        }
        updateThemeBtn(document.documentElement.getAttribute('data-theme') || 'light');

        function openSidebar() {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('sbOverlay').classList.add('visible');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sbOverlay').classList.remove('visible');
        }

        function toggleUserMenu() {
            document.getElementById('userDropdown').classList.toggle('open');
        }
        document.addEventListener('click', function(e) {
            const user = document.querySelector('.sb-user');
            const dd = document.getElementById('userDropdown');
            if (user && !user.contains(e.target)) dd.classList.remove('open');
        });
    </script>
</body>

</html>