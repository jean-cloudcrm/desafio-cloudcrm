<?php

namespace App\Http\Controllers;
use App\Models\Movimentacao;
use App\Models\Cadastro;

use Illuminate\Http\Request;

class MovimentacoesController extends Controller
{

    public function index(Request $request){
        $formasPagamento = $request->input('formas_pagamento', null);

        if ($formasPagamento) {
            $movimentacoes = Movimentacao::where('formas_pagamento', $formasPagamento)->paginate(5);
        
            if ($movimentacoes->isEmpty()) {
                return response()->json(['message' => 'Movimentação não encontrada'], 404);
            }
        
            return response()->json($movimentacoes, 200);
        } else {
            $movimentacoes = Movimentacao::with('cadastro')->paginate(5);
        
            if ($movimentacoes->isEmpty()) {
                return response()->json(['message' => 'Cadastro não encontrado'], 404);
            }
        
            return response()->json($movimentacoes, 200);
        }
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'produtos' => 'required|array',
            'produtos.*.nome' => 'required|string', // Nome é obrigatório e deve ser uma string
            'produtos.*.quantidade' => 'required|integer|min:1', // Quantidade é obrigatória, deve ser um inteiro e maior que 0
            'produtos.*.valor' => 'required|numeric|min:0', // Valor é obrigatório, deve ser um número e maior ou igual a 0
            'formas_pagamento' => 'required|string',
            'cadastro_id' => 'required|exists:cadastros,id',
            'bloqueado' => 'boolean',
        ], [
            'produtos.required' => 'O campo produtos é obrigatório.',
            'produtos.*.nome.required' => 'O nome do produto é obrigatório.',
            'produtos.*.quantidade.required' => 'A quantidade do produto é obrigatória.',
            'produtos.*.quantidade.integer' => 'A quantidade deve ser um número inteiro.',
            'produtos.*.quantidade.min' => 'A quantidade deve ser pelo menos 1.',
            'produtos.*.valor.required' => 'O valor do produto é obrigatório.',
            'produtos.*.valor.numeric' => 'O valor deve ser um número.',
            'produtos.*.valor.min' => 'O valor deve ser pelo menos 0.',
            'formas_pagamento.required' => 'O campo formas_pagamento é obrigatório.',
            'cadastro_id.required' => 'O campo cadastro_id é obrigatório.',
            'cadastro_id.exists' => 'O cadastro selecionado não existe.',
            'bloqueado.boolean' => 'O campo bloqueado deve ser verdadeiro ou falso.',
        ]);
    
        // Se a validação passar, cria a movimentação
        $movimentacoes = Movimentacao::create($validatedData);
    
        return response()->json($movimentacoes, 201);
    }
}