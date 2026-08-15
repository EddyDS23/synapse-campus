@extends('layouts.guest')

@section('title', 'Iniciar sesión | Synapse Campus')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<style>
    :root {
        --bg: #020617;
        --card-bg: rgba(30, 41, 59, 0.5);
        --accent: #6366f1;
    }

    body {
        background-color: var(--bg);
        color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
        /* Asegura que el contenedor ocupe el alto de la pantalla para centrar la tarjeta */
        min-height: 100vh; 
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .synapse-glass {
        background: var(--card-bg);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -5px rgba(99, 102, 241, 0.6);
    }
</style>

<div class="w-full max-w-md mx-auto p-4">
    <!-- Tarjeta Glassmorphism -->
    <div class="synapse-glass p-8 shadow-2xl border-t-2 border-t-indigo-500">
        
        <!-- Cabecera y Logo -->
        <div class="text-center mb-8 flex flex-col items-center">
            
            
            <h1 class="text-3xl font-black text-white tracking-tight">
                Synapse<span class="text-indigo-500">Campus</span>
            </h1>
            <p class="text-sm text-slate-400 mt-2 font-medium">
                Ingresa con tu cuenta institucional.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-xs font-bold animate-pulse text-center">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        {{-- Formulario principal --}}
        <form method="POST" action="/login" class="space-y-5">
            @csrf

            <div>
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">
                    Correo institucional
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    autofocus
                    required
                    placeholder="usuario@ejemplo.com"
                    class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all text-sm shadow-inner placeholder-slate-600">
            </div>

            <div>
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-1.5">
                    Contraseña
                </label>
                <input
                    type="password"
                    name="password"
                    required
                    placeholder="••••••••"
                    class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all text-sm shadow-inner placeholder-slate-600">
            </div>

            <button type="submit" class="btn-primary w-full text-white font-bold py-3 rounded-xl text-sm mt-2">
                Iniciar sesión
            </button>
        </form>

        {{-- Divisor --}}
        <div class="flex items-center gap-4 my-6">
            <div class="flex-1 bg-slate-800 h-px"></div>
            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">O continúa con</span>
            <div class="flex-1 bg-slate-800 h-px"></div>
        </div>

        {{-- OAuth GitHub --}}
        <a href="{{ route('oauth.redirect', 'github') }}"
            class="flex items-center justify-center gap-3 w-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 py-3 rounded-xl transition-colors text-sm font-bold shadow-sm">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
            </svg>
            Continuar con GitHub
        </a>

    </div>
</div>
@endsection