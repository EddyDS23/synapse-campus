@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
    <h1>Mi Perfil Academico</h1>
    
    <div>
        <p><strong>Numero de Control: </strong>{{ $number }}</p>
        <p><strong>Carrera: </strong>{{ $career }}</p>
        <p><strong>Semestre: </strong>{{ $semester }}</p>
        <p><strong>Estado: </strong>{{ $status }}</p>
        <p><strong>Adeudo: </strong>{{ $has_debt ? 'Si' : 'No' }}</p>
    </div>

@endsection