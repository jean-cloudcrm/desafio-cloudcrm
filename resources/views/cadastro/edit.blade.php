@extends('layouts.app')

@section('content')
{{-- Perfeito --}}
<div class="mt-5 container">
  <h1>Edição</h1>
  <hr>
  <form action="{{route('cadastro-update',['id'=>$cadastros->id])}}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
      <div class="form-group">
        <label for="nome">Nome:</label>
        <input type="text" class="form-control" name="nome" value="{{$cadastros->nome}}" placeholder="Digite um nome">
      </div>
      <div class="form-group">
        <label for="email">E-mail:</label>
        <input type="text" class="form-control" name="email" value="{{$cadastros->email}}" placeholder="Digite um email">
      </div>
      <div class="form-group">
        <label for="birthday">Aniversário:</label>
        <input type="date" class="form-control" name="birthday" value="{{$cadastros->birthday}}">
      </div>
      <br>
      <div class="form-group">
        <input type="submit" class="btn btn-success" name="submit" value="Atualizar">
      </div>
    </div>
  </form>
</div>

@endsection