<?php

namespace App\Http\Controllers;
use App\Models\Movimentacao;
use App\Models\Cadastro;
use App\Models\Produto;
use League\Csv\Writer;
use League\Csv\CharsetConverter;
use Carbon\Carbon;

use Illuminate\Http\Request;

class MovimentacoesController extends Controller
{

    public function index(Request $request)
{
    $formasPagamento = $request->input('formas_pagamento', null);

    if ($formasPagamento) {
        $movimentacoes = Movimentacao::with('produtos', 'cadastro') 
            ->where('formas_pagamento', $formasPagamento)
            ->paginate(5);

        if ($movimentacoes->isEmpty()) {
            return response()->json(['message' => 'Não há movimentações cadastradas'], 404);
        }

        return response()->json($movimentacoes, 200);
    } else {
        $movimentacoes = Movimentacao::with('produtos', 'cadastro')->paginate(5);

        if ($movimentacoes->isEmpty()) {
            return response()->json(['message' => 'Não há movimentações cadastradas'], 404);
        }

        return response()->json($movimentacoes, 200);
    }
}

public function export(Request $request)
{
    if ($request->input('ultimos30dias')) {
        $dataLimite = \Carbon\Carbon::now()->subDays(30);
        $movimentacoes = Movimentacao::with('cadastro', 'produtos')
            ->whereBetween('created_at', [$dataLimite, \Carbon\Carbon::now()])
            ->get();
    } elseif ($mes = $request->input('mes') && $ano = $request->input('ano')) {
        $movimentacoes = Movimentacao::with('cadastro', 'produtos')
            ->whereYear('created_at', $ano)
            ->whereMonth('created_at', $mes)
            ->get();
    } else {
        $movimentacoes = Movimentacao::with('cadastro', 'produtos')->get();
    }

    $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
    $csv->insertOne(['nome_produto', 'quantidade_produto', 'valor_produto', 'formas_pagamento', 'nome_usuario', 'email_usuario', 'data_nascimento_usuario', 'bloqueado', 'created_at']);

    foreach ($movimentacoes as $movimentacao) {
        foreach ($movimentacao->produtos as $produto) {
            $csv->insertOne([
                $produto->nome ?? 'N/A',
                $produto->quantidade ?? 'N/A',
                $produto->valor ?? 'N/A',
                $movimentacao->formas_pagamento,
                $movimentacao->cadastro->nome ?? 'N/A',
                $movimentacao->cadastro->email ?? 'N/A',
                $movimentacao->cadastro->birthday ?? 'N/A',
                $movimentacao->bloqueado ? 'SIM' : 'NÃO',
                $movimentacao->created_at,
            ]);
        }
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="movimentacoes.csv"');
    $csv->output();
    exit;
}


    
    // public function somaCredito(Request $request){
    //     $formasPagamento = $request->input('formas_pagamento', null);
    //     if($formasPagamento == 'credito'){
    //         $movimentacoes = Movimentacao::with('cadastro')->where('formas_pagamento', $formasPagamento)->sum('');
    //     }
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cadastro_id' => 'required|exists:cadastros,id', 
            'formas_pagamento' => 'required|in:credito,debito,boleto,pix',
            'bloqueado' => 'boolean',
            'produtos' => 'required|array',
            'produtos.*' => 'required|exists:produtos,id', 
        ]);
    
        $movimentacao = Movimentacao::create([
            'cadastro_id' => $validated['cadastro_id'],
            'formas_pagamento' => $validated['formas_pagamento'],
            'bloqueado' => $validated['bloqueado'],
        ]);
    
        foreach ($validated['produtos'] as $produtoId) {
            $movimentacao->produtos()->attach($produtoId);
        }
    
        return response()->json(['message' => 'Movimentação criada com sucesso!'], 201);
    }
    

    public function destroy(Request $request, $id){
        $movimentacao = Movimentacao::find($id);
        if(!$movimentacao) {
            return response()->json(['message' => 'Movimentação não encontrada'], 404);
        }   
        else {
            $movimentacao->delete();
            return response()->json(['message' => 'Movimentacao excluída com sucesso'], 200);
        }   
    }
}