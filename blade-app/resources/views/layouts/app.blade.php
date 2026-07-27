<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title','Synapse Campus')</title>
</head>
<body>
    <nav>
        <a href="/dashboard">Synapse Campus</a>
    
        <ul>
            {{-- Estudiante --}}
            @if (RoleHelper::hasRole('student'))
                <li><a href="/profile">Mi Perfil</a></li>
                <li><a href="/schedule">Mi Horario</a></li>
                <li><a href="/subjects">Mis Materias</a></li>
                <li><a href="/biblioteca/prestamos">Mis Préstamos</a></li>
                <li><a href="/biblioteca/multas">Mis Multas</a></li>
                <li><a href="/soporte/mis-tickets">Mis Tickets</a></li>
            @endif

            {{-- Bibliotecario --}}
            @if (RoleHelper::isLibrarian())
                 <li><a href="/biblioteca/inventario">Inventario</a></li>                
            @endif

            {{-- Agente de Soporte --}}
            @if (RoleHelper::isAgent())
                <li><a href="/soporte/tickets">Tickets</a></li>
            @endif

        </ul>

        <span>{{ session('email') }}</span>

        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit">Cerrar Sesion</button>
        </form>
    </nav>

    <main>
        @yield('content')
    </main>
</body>
</html>