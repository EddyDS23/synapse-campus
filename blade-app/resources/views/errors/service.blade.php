@extends('layouts.guest')

@section('title', 'Servicio no disponible')

@section('content')
    <h2>Servicio no disponible</h2>
    <p>{{ $message }}</p>
    <a href="/dashboard">Volver al dashboard</a>    
@endsection
