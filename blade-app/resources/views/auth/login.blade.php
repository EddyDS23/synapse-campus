@extends('layouts.guest')

@section('title','Synapse Campus - Login')

@section('content')
   @if ($errors->any())
       <p> {{ $errors->first() }} </p>
   @endif

    <form action="/login" method="post">
        @csrf
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Correo" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>

@endsection