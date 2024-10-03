<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;
// OK, mas aqui poderiamos utilizar requests do laravel e simplificar o else;
class ProdutosController extends Controller
{
    public function index(Request $request){
        $produtos = Produto::all();
        if($produtos){
            return response()->json($produtos, 200);
        } else {
            return response(['message' => 'Cadastro não encontrado', 400]);
        }
        //Else aqui e desnecessario poderiamos inverter a condicao e remover o else
    }

    public function store(Request $request)
    {
    //Poderiamos aqui criar uma Request personalizada ProdutosControllerRequest, e apenas chamarmos $request->validated();
    $validatedData = $request->validate([
        'nome' => 'required|string',
        'quantidade' => 'required|integer|min:1',
        'valor' => 'required|numeric|min:0.01',
    ]);
    $produtos = Produto::create($validatedData);
    return response()->json($produtos, 201);
    }
}
