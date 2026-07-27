@extends('layouts.app')

@section('title', 'Materias')

@section('content')
    <h1>Materias por Semestre</h1>

    @if(empty($career_subjects))
        <p>No tienes materias registradas.</p>
    @else
        @foreach($career_subjects as $semester)
            <h3>Semestre {{ $semester['semester'] }}</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Código</th>
                        <th>Créditos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semester['subjects'] as $subject)
                        <tr>
                            <td>{{ $subject['name'] }}</td>
                            <td>{{ $subject['code'] }}</td>
                            <td>{{ $subject['credits'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endif
@endsection