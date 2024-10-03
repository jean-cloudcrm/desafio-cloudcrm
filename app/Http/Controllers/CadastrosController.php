<?php

namespace App\Http\Controllers;

use App\Models\Cadastro;
use Illuminate\Http\Request;
use Carbon\Carbon;

//Funcional, mas poderiamos utilizar mais laravel aqui, utilizar collections, requests, evitar querys, e simplififcar condicionais;
class CadastrosController extends Controller
{
    public function index(Request $request)
    {
    $cadastros = Cadastro::all()->sortByDesc('created_at');

    if ($request->expectsJson()) {
        return Cadastro::all()->sortByDesc('created_at');
    }
    return view('cadastro.index', ['cadastros' => $cadastros]);
    }
    //Aqui poderia ser utilizado a model evitando essa query de cadastro, poderiamos receber na request Cadastro $cadastro;
    public function show(Request $request, $id)
    {
        $cadastro = Cadastro::find($id);
    
        if (!$cadastro) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cadastro não encontrado'], 404);
            }
            //Poderia utilizar to_route
            return redirect()->route('cadastro-index');
        }
    
        if ($request->expectsJson()) {
            return response()->json($cadastro, 200);
        } else {
            return response()->json(['message' => 'Cadastro não encontrado'], 404);
        }
    
        return view('cadastro.show', ['cadastro' => $cadastro]);
    }

    public function create()
    {
        return view('cadastro.create');
    }


    public function store(Request $request)
{
    if ($request->expectsJson()) {
        //Poderia criar um arquivo para Validacao de request, chamando apenas $request->validated(), php artisan make:request StoreCadastroRequest
        $validatedData = $request->validate([
            'nome' => 'required|string',
            'email' => 'required|string|email',
            'birthday' => 'required|date_format:d/m/Y',
        ]);
        
        $birthday = \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['birthday']);

        if ($birthday->diffInYears() < 18) {
            return response()->json(['message' => 'Somente maiores de 18 anos podem ser cadastrados.'], 403);
        }
        $cadastro = Cadastro::create(array_merge($validatedData, [
            'birthday' => $birthday 
        ]));

        return response()->json($cadastro, 201); 
    }

    return redirect()->route('cadastro-index');
}
    //Aqui poderia ser utilizado a model evitando essa query de cadastro, poderiamos receber na request Cadastro $cadastro;
    public function edit($id)
    {
        $cadastros = Cadastro::where('id', $id)->first();
        if(!empty($cadastros))
        {
            return view('cadastro.edit', ['cadastros'=>$cadastros]);
        }
        else{
            return redirect()->route('cadastro-index');  
        }
    }

    public function update(Request $request, $id)
    {   
    $cadastro = Cadastro::find($id); 
    if ($cadastro) {
        $cadastro->nome = $request->input('nome');
        $cadastro->email = $request->input('email');
        $cadastro->birthday = $request->input('birthday');
        $cadastro->save();
        
        if ($request->expectsJson()) {
            return response()->json($cadastro, 200);
        }
        return redirect()->route('cadastro-index');
    }
    return response()->json(['message' => 'Cadastro não encontrado'], 404);
}


    public function destroy(Request $request, $id)
    {   
        $cadastro = Cadastro::find($id);
        if (!$cadastro) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cadastro não encontrado'], 404);
            }
            return redirect()->route('cadastro-index');
        }

        if ($cadastro->movimentacao()->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Não é possível excluir o cadastro porque tem movimentações relacionadas a esse cadastro.'], 403);
            }
            return redirect()->route('cadastro-index')->with('error', 'Não é possível excluir o cadastro, pois existem movimentações associadas.');
        }

        $cadastro->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' =>'Cadastro excluído com sucesso'], 200);
        }
        //poderiamos utilizar to_route() shortcut
        return redirect()->route('cadastro-index');
    }
}
