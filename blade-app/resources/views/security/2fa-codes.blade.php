@extends('layouts.app')

@section('title', '2FA Activado')

@section('content')
<div style="max-width:480px; margin:0 auto;">

    <div class="page-header">
        <div>
            <div class="page-title">2FA activado correctamente</div>
            <div class="page-subtitle">Guarda tus códigos de recuperación en un lugar seguro.</div>
        </div>
    </div>

    <div class="card mb-18" style="border-color:var(--ok);">
        <div class="card-body">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--ok)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span style="font-size:13px; font-weight:600; color:var(--ok);">Autenticación 2FA activada</span>
            </div>
            <p style="font-size:12px; color:var(--text-sub);">
                A partir de ahora necesitarás tu app autenticadora cada vez que inicies sesión.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Códigos de recuperación</div>
                <div class="card-subtitle">Úsalos si pierdes acceso a tu app autenticadora. Cada código solo funciona una vez.</div>
            </div>
        </div>
        <div class="card-body">

            <div style="background:var(--elevated); border:1px solid var(--border); border-radius:var(--radius-md); padding:16px; margin-bottom:16px;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    @foreach($codes as $code)
                        <div style="font-family:var(--mono); font-size:13px; color:var(--text); background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-sm); padding:8px 12px; letter-spacing:1px;">
                            {{ $code }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="padding:12px; background:var(--warn-dim); border:1px solid rgba(240,169,70,.25); border-radius:var(--radius-sm); margin-bottom:16px;">
                <p style="font-size:12px; color:var(--warn);">
                    Guarda estos códigos ahora. No podrás verlos de nuevo. Si los pierdes y pierdes acceso a tu app autenticadora, no podrás entrar a tu cuenta.
                </p>
            </div>

            <a href="{{ route('security.index') }}" class="btn btn-primary" style="width:100%; justify-content:center;">
                Entendido, ya los guardé
            </a>
        </div>
    </div>

</div>
@endsection