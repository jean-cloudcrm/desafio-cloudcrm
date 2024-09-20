@extends('layouts.app')

@section('content')
<div class="mt-5 container">
  <h1>Cadastre um novo usuário:</h1>
  <hr>
  <form action="{{route('cadastro-store')}}" method="POST">
    @csrf
    <div class="form-group">
      <div class="form-group">
        <label for="nome">Nome:</label>
        <input type="text" class="form-control" name="nome" placeholder="Digite um nome">
      </div>
      <div class="form-group">
        <label for="email">E-mail:</label>
        <input type="text" class="form-control" name="email" placeholder="Digite um email">
      </div>
      <div class="form-group">
        <label for="birthday">Aniversário:</label>
        <input type="date" class="form-control" name="birthday">
      </div>
      <br>
      <div class="form-group">
        <input type="submit" class="btn btn-primary" name="submit">
      </div>
    </div>
  </form>
</div>

@endsection