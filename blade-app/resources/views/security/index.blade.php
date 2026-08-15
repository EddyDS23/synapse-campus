@extends('layouts.app')

@section('title', 'Seguridad')

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Seguridad de la cuenta</div>
        <div class="page-subtitle">Gestiona tus sesiones activas, autenticación de dos factores y actividad reciente.</div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="grid-2">

    {{-- ── Sesiones activas ── --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Sesiones activas</div>
                <div class="card-subtitle">Dispositivos con sesión iniciada actualmente.</div>
            </div>
        </div>
        <div class="card-body" style="padding-top:12px;">
            @forelse($sessions as $session)
                <div style="display:flex; align-items:flex-start; gap:12px; padding:12px 0; border-bottom:1px solid var(--border);">
                    <div style="width:36px; height:36px; background:var(--elevated); border:1px solid var(--border); border-radius:var(--radius-sm); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--text-sub)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:12px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $session['device'] ?? 'Dispositivo desconocido' }}
                        </div>
                        <div style="font-size:11px; color:var(--text-sub); margin-top:2px;">
                            {{ $session['ip_address'] }}
                            &mdash;
                            Expira {{ \Carbon\Carbon::parse($session['expires_at'])->diffForHumans() }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('security.session.delete', $session['id']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Cerrar</button>
                    </form>
                </div>
            @empty
                <p style="font-size:13px; color:var(--text-muted); text-align:center; padding:20px 0;">
                    No hay sesiones activas.
                </p>
            @endforelse
        </div>
    </div>

    {{-- ── Autenticación 2FA ── --}}
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Autenticación de dos factores</div>
                <div class="card-subtitle">Agrega una capa extra de seguridad a tu cuenta.</div>
            </div>
        </div>
        <div class="card-body">

            {{-- Activar 2FA --}}
            <div style="padding:16px; background:var(--elevated); border:1px solid var(--border); border-radius:var(--radius-md); margin-bottom:14px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>
                    </svg>
                    <span style="font-size:13px; font-weight:600; color:var(--text);">App autenticadora</span>
                </div>
                <p style="font-size:12px; color:var(--text-sub); margin-bottom:14px;">
                    Usa Google Authenticator, Authy u otra app compatible con TOTP para generar códigos de verificación.
                </p>
                <form method="POST" action="{{ route('security.2fa.enable') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        Activar 2FA
                    </button>
                </form>
            </div>

            {{-- Desactivar 2FA --}}
            <div style="padding:16px; background:var(--elevated); border:1px solid var(--border); border-radius:var(--radius-md);">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>
                    </svg>
                    <span style="font-size:13px; font-weight:600; color:var(--text);">Desactivar 2FA</span>
                </div>
                <p style="font-size:12px; color:var(--text-sub); margin-bottom:14px;">
                    Ingresa tu código actual para confirmar la desactivación.
                </p>
                <form method="POST" action="{{ route('security.2fa.disable') }}" style="display:flex; gap:8px;">
                    @csrf
                    <input type="text" name="code" placeholder="Código TOTP" maxlength="10"
                        style="flex:1; height:30px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-sm); padding:0 10px; color:var(--text); font-size:12px; font-family:var(--mono); outline:none;">
                    <button type="submit" class="btn btn-danger btn-sm">Desactivar</button>
                </form>
                @error('code')
                    <p style="font-size:11px; color:var(--danger); margin-top:6px;">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>

</div>

{{-- ── Actividad reciente ── --}}
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Actividad reciente</div>
            <div class="card-subtitle">Últimas acciones registradas en tu cuenta.</div>
        </div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Acción</th>
                    <th>Servicio</th>
                    <th>IP</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                    <tr>
                        <td class="td-primary">{{ $log['action'] }}</td>
                        <td>
                            @if($log['service'])
                                <span class="tag badge-neutral">{{ $log['service'] }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td class="td-mono">{{ $log['ip_address'] }}</td>
                        <td style="color:var(--text-sub); font-size:12px;">
                            {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:24px; color:var(--text-muted);">
                            No hay actividad registrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection