<?php

namespace App\Http\Controllers;
use App\Models\Movimentacao;
use App\Models\Cadastro;
use App\Models\Produto;
use League\Csv\Writer;
use League\Csv\CharsetConverter;
use Carbon\Carbon;

use Illuminate\Http\Request;
//Funcional, mas poderiamos utilizar mais laravel aqui, utilizar collections, requests, evitar querys, e simplififcar condicionais;
class MovimentacoesController extends Controller
{

    public function index(Request $request)
{
    //Devemos evitar sempre receber valores da reqest dessa forma, ao utilizar validated() conseguimos diminuir o codigo e garantir maior integridade dos dados recebidos;
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
        // poderia utilizar apenas now()
        $dataLimite = \Carbon\Carbon::now()->subDays(30);
        $movimentacoes = Movimentacao::with('cadastro', 'produtos')
            ->whereBetween('created_at', [$dataLimite, \Carbon\Carbon::now()])
            ->get();
    } elseif ($request->input('mes') && $request->input('ano')) {
        $mes = $request->input('mes');
        $ano = $request->input('ano');
        
        $movimentacoes = Movimentacao::with('cadastro', 'produtos')
            ->whereYear('created_at', $ano)
            ->whereMonth('created_at', $mes)
            ->get();
    } else {
        $movimentacoes = Movimentacao::with('cadastro', 'produtos')->get();
    }

    $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());
    $csv->insertOne(['id_movimentacao','nome_produto', 'quantidade_produto', 'valor_produto', 'formas_pagamento', 'nome_usuario', 'email_usuario', 'data_nascimento_usuario', 'bloqueado', 'created_at']);

    foreach ($movimentacoes as $movimentacao) {
        foreach ($movimentacao->produtos as $produto) {
            $csv->insertOne([
                $movimentacao->id,
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
    
public function totalCredito()
{
    $movimentacoes = Movimentacao::with('produtos', 'cadastro')
        ->where('formas_pagamento', 'credito')
        ->get();

    if ($movimentacoes->isEmpty()) {
        return response()->json(['message' => 'Nenhuma movimentação encontrada para pagamento em crédito.'], 404);
    }

    $totalPorUsuario = [];
    //Funcional mas poderiamos usar a sintaxe each e trabalharmos com Collections do laravel;
    foreach ($movimentacoes as $movimentacao) {
        if (!$movimentacao->cadastro) {
            continue;
        }

        $usuarioId = $movimentacao->cadastro->id;

        if (!isset($totalPorUsuario[$usuarioId])) {
            $totalPorUsuario[$usuarioId] = [
                'id' => $usuarioId,
                'nome' => $movimentacao->cadastro->nome,
                'email' => $movimentacao->cadastro->email,
                'total_credito' => 0 ,
            ];
        }

        foreach ($movimentacao->produtos as $produto) {
            $totalPorUsuario[$usuarioId]['total_credito'] += $produto->valor * $produto->quantidade;
        }
    }

    return response()->json(array_values($totalPorUsuario), 200);
}

    public function totalDebito(){
        $movimentacoes = Movimentacao::with('produtos', 'cadastro')->where('formas_pagamento', 'debito')->get();

        if($movimentacoes->isEmpty()){
            return response()->json(['message' => 'Nenhuma movimentação encontrada para pagamento em débito.'], 404);
        }

        $totalPorUsuario = [];
        //Funcional mas poderiamos usar a sintaxe each e trabalharmos com Collections do laravel;
        foreach ($movimentacoes as $movimentacao){
            if(!$movimentacao->cadastro){
                continue;
            }

            $usuarioId = $movimentacao->cadastro->id;

            if(!isset($totalPorUsuario[$usuarioId])){
                $totalPorUsuario[$usuarioId] = [
                    'id' => $usuarioId,
                    'nome' => $movimentacao->cadastro->nome,
                    'email' => $movimentacao->cadastro->email,
                    'total_debito' => 0
                ];
            }

            foreach ($movimentacao->produtos as $produto) {
                $totalPorUsuario[$usuarioId]['total_debito'] += $produto->valor * $produto->quantidade;
            }
        }
        return response()->json(array_values($totalPorUsuario), 200);
    }

    public function totalCreditoDebito() {
        //Aqui voce poderia fazer apenas uma request de movimentacoes, e depois filtrar a colection com where
        $movimentacoesC = Movimentacao::with('produtos', 'cadastro')->where('formas_pagamento', 'credito')->get();
        $movimentacoesD = Movimentacao::with('produtos', 'cadastro')->where('formas_pagamento', 'debito')->get();
    
        if ($movimentacoesC->isEmpty() && $movimentacoesD->isEmpty()) {
            return response()->json(['message' => 'Nenhuma movimentação encontrada para pagamentos em débito e/ou crédito.'], 404);
        }
    
        $totalPorUsuario = [];
        //Funcional mas poderiamos usar a sintaxe each e trabalharmos com Collections do laravel;
        foreach ($movimentacoesC as $movimentacao) {
            if (!$movimentacao->cadastro) {
                continue;
            }
    
            $usuarioId = $movimentacao->cadastro->id;
    
            if (!isset($totalPorUsuario[$usuarioId])) {
                $totalPorUsuario[$usuarioId] = [
                    'id' => $usuarioId,
                    'nome' => $movimentacao->cadastro->nome,
                    'email' => $movimentacao->cadastro->email,
                    'total_credito' => 0,
                    'total_debito' => 0, 
                    'total' => 0 
                ];
            }
    
            foreach ($movimentacao->produtos as $produto) {
                $totalPorUsuario[$usuarioId]['total_credito'] += $produto->valor * $produto->quantidade;
            }
        }
    
        foreach ($movimentacoesD as $movimentacao) {
            if (!$movimentacao->cadastro) {
                continue;
            }
    
            $usuarioId = $movimentacao->cadastro->id;
    
            if (!isset($totalPorUsuario[$usuarioId])) {
                $totalPorUsuario[$usuarioId] = [
                    'id' => $usuarioId,
                    'nome' => $movimentacao->cadastro->nome,
                    'email' => $movimentacao->cadastro->email,
                    'total_credito' => 0,
                    'total_debito' => 0,
                    'total' => 0
                ];
            }
    
            foreach ($movimentacao->produtos as $produto) {
                $totalPorUsuario[$usuarioId]['total_debito'] += $produto->valor * $produto->quantidade;
            }
        }
    
        foreach ($totalPorUsuario as $usuarioId => $totais) {
            $totalPorUsuario[$usuarioId]['total'] = $totais['total_credito'] + $totais['total_debito'];
        }
    
        return response()->json(array_values($totalPorUsuario), 200);
    }
    
    public function store(Request $request)
{
    //Novamente aqui poderiamos criar uma Request personalizada para esse metodo chamando  apenas $request->validated()
    $validated = $request->validate([
        'cadastro_id' => 'required|exists:cadastros,id', 
        'formas_pagamento' => 'required|in:credito,debito,boleto,pix',
        'bloqueado' => 'boolean',
        'produtos' => 'required|array',
        'produtos.*' => 'required|exists:produtos,id', 
    ]);

    if ($validated['bloqueado']) {
        return response()->json(['message' => 'A movimentação está bloqueada e não pode ser cadastrada.'], 403);
    }

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
        //poderia ter evitado esse else
    }
}