@extends('layouts.app')

@section('title', 'Activar 2FA')

@section('content')
<div style="max-width:480px; margin:0 auto;">

    <div class="page-header">
        <div>
            <div class="page-title">Activar autenticación 2FA</div>
            <div class="page-subtitle">Escanea el código QR con tu app autenticadora.</div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    {{-- Paso 1: QR --}}
    <div class="card mb-18">
        <div class="card-body">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                <div style="width:24px; height:24px; background:var(--accent); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#fff; flex-shrink:0;">1</div>
                <span style="font-size:13px; font-weight:600;">Escanea el código QR</span>
            </div>
            <p style="font-size:12px; color:var(--text-sub); margin-bottom:16px;">
                Abre Google Authenticator, Authy o cualquier app compatible con TOTP y escanea este código.
            </p>

            @if($qr)
                <div style="display:flex; justify-content:center; padding:16px; background:var(--elevated); border:1px solid var(--border); border-radius:var(--radius-md);">
                    <img src="data:image/svg+xml;base64,{{ $qr }}" alt="QR Code 2FA" style="width:180px; height:180px;">
                </div>
            @else
                <div style="text-align:center; padding:32px; color:var(--text-muted);">
                    No se pudo generar el código QR.
                </div>
            @endif
        </div>
    </div>

    {{-- Paso 2: Verificar --}}
    <div class="card">
        <div class="card-body">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                <div style="width:24px; height:24px; background:var(--accent); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#fff; flex-shrink:0;">2</div>
                <span style="font-size:13px; font-weight:600;">Verifica el código</span>
            </div>
            <p style="font-size:12px; color:var(--text-sub); margin-bottom:16px;">
                Ingresa el código de 6 dígitos que muestra tu app para confirmar que la configuración es correcta.
            </p>

            <form method="POST" action="{{ route('security.2fa.verify') }}">
                @csrf
                <div style="margin-bottom:14px;">
                    <input type="text" name="code" autofocus maxlength="6" placeholder="000000"
                        style="width:100%; height:42px; background:var(--elevated); border:1px solid var(--border); border-radius:var(--radius-sm); padding:0 14px; color:var(--text); font-size:22px; font-family:var(--mono); letter-spacing:8px; text-align:center; outline:none;">
                    @error('code')
                        <p style="font-size:11px; color:var(--danger); margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    Confirmar y activar 2FA
                </button>
            </form>
        </div>
    </div>

    <div style="margin-top:16px; text-align:center;">
        <a href="{{ route('security.index') }}" style="font-size:12px; color:var(--text-sub);">Cancelar</a>
    </div>

</div>
@endsection