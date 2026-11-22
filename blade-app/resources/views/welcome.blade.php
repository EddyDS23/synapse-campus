@extends('layouts.guest')

@section('title', 'Synapse Campus')

@section('content')
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:var(--color-background, #F8FAFC); font-family:var(--font-sans, system-ui); padding:2rem;">
    <div style="text-align:center; max-width:32rem;">
        <h1 style="font-family:var(--font-serif, Georgia); font-size:2.5rem; font-weight:700; color:var(--color-foreground, #0F172A); margin-bottom:0.5rem;">
            Synapse<span style="color:var(--color-primary, #1E3A5F);">Campus</span>
        </h1>
        <p style="color:var(--color-muted-foreground, #475569); margin-bottom:2rem;">
            Portal institucional del campus. Inicia sesión para continuar.
        </p>
        <a href="{{ route('login') }}" style="display:inline-block; padding:0.75rem 1.5rem; background:var(--color-primary, #1E3A5F); color:#fff; border-radius:0.625rem; font-weight:600; text-decoration:none;">
            Iniciar sesión
        </a>
    </div>
</div>
@endsection
