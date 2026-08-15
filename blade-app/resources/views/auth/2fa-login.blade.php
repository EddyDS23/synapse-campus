@extends('layouts.guest')

@section('title', 'Verificación 2FA | Synapse Campus')

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
        
        <!-- Cabecera -->
        <div class="text-center mb-8 flex flex-col items-center">
            <div class="w-14 h-14 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-full flex items-center justify-center text-2xl mb-4">
                🛡️
            </div>
            
            <h1 class="text-2xl font-black text-white tracking-tight">
                Verificación en <span class="text-indigo-400">dos pasos</span>
            </h1>
            <p class="text-sm text-slate-400 mt-2 font-medium">
                Ingresa el código de 6 dígitos de tu aplicación autenticadora.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-xs font-bold animate-pulse text-center">
                ⚠️ {{ $errors->first() }}
            </div>
        @endif

        {{-- Formulario principal --}}
        <form method="POST" action="{{ route('login.2fa') }}" class="space-y-6">
            @csrf

            <div>
                <label class="block text-[10px] text-slate-400 uppercase font-bold mb-2 text-center">
                    Código TOTP
                </label>
                <input
                    type="text"
                    name="code"
                    autofocus
                    maxlength="10"
                    placeholder="000000"
                    class="w-full bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-4 text-slate-100 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition-all shadow-inner placeholder-slate-700 text-center text-3xl font-mono tracking-[0.25em]">
            </div>

            <button type="submit" class="btn-primary w-full text-white font-bold py-3.5 rounded-xl text-sm">
                Verificar Código
            </button>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-xs font-bold text-slate-500 hover:text-indigo-400 transition-colors">
                    ← Volver al login
                </a>
            </div>
        </form>

        {{-- Caja de información adicional --}}
        <div class="mt-8 p-4 bg-slate-900/30 border border-slate-800/50 rounded-xl text-center">
            <p class="text-xs text-slate-400">
                ¿No tienes acceso a tu app? Usa uno de tus 
                <strong class="text-indigo-300 font-bold">códigos de recuperación</strong> en su lugar.
            </p>
        </div>

    </div>
</div>
@endsection