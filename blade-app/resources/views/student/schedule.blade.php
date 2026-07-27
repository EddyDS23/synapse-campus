@extends('layouts.app')

@section('title', 'Horario')

@section('content')
    <h1>Mi Horario</h1>

    @if (empty($schedules))
        <p>No tienes clases asignadas</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Materia</th>
                    <th>Dia</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Grupo</th>
                    <th>Profesor</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schedules as $schedule)
                    <tr>
                        <td>{{ $schedule['subject_name'] }}</td>
                        <td>{{ $schedule['day_of_week'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}</td>
                        <td>{{ $schedule['group'] }}</td>
                        <td>{{ $schedule['teacher'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
