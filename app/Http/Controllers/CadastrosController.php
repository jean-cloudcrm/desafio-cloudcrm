<?php

namespace App\Http\Controllers;

use App\Models\Cadastro;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    public function show(Request $request, $id)
    {
        $cadastro = Cadastro::find($id);
    
        if (!$cadastro) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cadastro não encontrado'], 404);
            }
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
    
    if($request->expectsJson()){
        $validatedData = $request->validate([
            'nome' => 'required|string',
            'email' => 'required|string',
            'birthday' => 'required|date_format:d/m/Y',
        ]);
        $validatedData['birthday']= \Carbon\Carbon::createFromFormat('d/m/Y',$validatedData['birthday'])->format('Y-m-d');

        $cadastro = Cadastro::create($validatedData);
        return response()->json($cadastro, 201); 
    }    
    
    return redirect()->route('cadastro-index');
    }

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
        $cadastro->delete();
        if ($request->expectsJson()) {
            return response()->json(['message' =>'Cadastro excluído com sucesso'], 200);
        }
        return redirect()->route('cadastro-index');
    }
}
