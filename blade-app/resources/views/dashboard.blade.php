@extends('layouts.app')

@section('title','Dashboard - Synapse Campus')

@section('content')
    <h1>Bienvenido {{ session('email') }}</h1>

    <div>
        @if (RoleHelper::hasRole('student')){}
            <div>
                <h3>Portal Academico</h3>
                <a href="/profile">Ver perfil</a>
                <a href="/schedule">Ver horario</a>
            </div>

            <div>
                <h3>Biblioteca</h3>
                <a href="/biblioteca/prestamos">Mis prestamos</a>
                <a href="/biblioteca/multas">Mis multas</a>
            </div>

            <div>
                <h3>Soporte</h3>
                <a href="/soporte/mis-tickets">Mis tickets</a>
                <a href="/soporte/tickets/nuevo">Nuevo Ticket</a>
            </div>
        @endif

        @if (RoleHelper::isAgent())
            <div>
                <h3>Mesa de Ayuda</h3>
                <a href="/soporte/tickets">Ver todos los tickets</a>
            </div>
            
        @endif

        @if (RoleHelper::isLibrarian())
            <h3>Biblioteca</h3>
            <a href="/biblioteca/inventario">Gestionar Inventario</a>
        @endif
    </div>

   

@endsection