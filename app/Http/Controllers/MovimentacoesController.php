<?php

namespace App\Http\Controllers;
use App\Models\Movimentacao;
use App\Models\Cadastro;
use League\Csv\Writer;
use League\Csv\CharsetConverter;
use Carbon\Carbon;

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

    public function export(Request $request)
    {
        $ultimos30Dias = $request->input('ultimos30dias', false);
        $mes = $request->input('mes', false);
        $ano = $request->input('ano', false);
    
        if ($ultimos30Dias) {
            $dataLimite = \Carbon\Carbon::now()->subDays(30);
            $hoje = \Carbon\Carbon::now();
    
            $movimentacoes = Movimentacao::with('cadastro')
                ->whereBetween('created_at', [$dataLimite, $hoje])
                ->get();
        }
        elseif ($mes && $ano) {
            if (checkdate($mes, 1, $ano)) {
                $movimentacoes = Movimentacao::with('cadastro')
                    ->whereYear('created_at', $ano)
                    ->whereMonth('created_at', $mes)
                    ->get();
            } else {
                return response()->json(['error' => 'Mês ou ano inválidos.'], 400);
            }
        } 
        else {
            $movimentacoes = Movimentacao::with('cadastro')->get();
        }
    
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
    
        if ($csv->supportsStreamFilterOnWrite()) {
            $csv->addStreamFilter(CharsetConverter::addTo('utf-8', 'iso-8859-1'));
        }
        $csv->insertOne(['nome_produto', 'quantidade_produto', 'valor_produto', 'formas_pagamento', 'nome_usuario', 'email_usuario', 'data_nascimento_usuario', 'bloqueado', 'created_at']);
    
        foreach ($movimentacoes as $movimentacao) {
            $produtos = is_array($movimentacao->produtos) ? $movimentacao->produtos : json_decode($movimentacao->produtos, true);
    
            if (!is_array($produtos)) {
                $produtos = [];
            }
    
            foreach ($produtos as $produto) {
                $nomeProduto = $produto['nome'] ?? 'N/A'; 
                $quantidadeProduto = $produto['quantidade'] ?? '0'; 
                $valorProduto = $produto['valor'] ?? '0.00'; 
                $bloqueado = $movimentacao->bloqueado ? 'SIM' : 'NÃO';
                $created_at = $movimentacao->created_at;
                $nomeUsuario = $movimentacao->cadastro->nome ?? 'N/A';
                $emailUsuario = $movimentacao->cadastro->email ?? 'N/A';
                $dataNascimentoUsuario = $movimentacao->cadastro->birthday ?? 'N/A';
    
                $csv->insertOne([
                    $nomeProduto,               
                    $quantidadeProduto,          
                    $valorProduto,               
                    $movimentacao->formas_pagamento, 
                    $nomeUsuario,              
                    $emailUsuario,             
                    $dataNascimentoUsuario,          
                    $bloqueado,
                    $created_at                 
                ]);
            }
        }
    
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="movimentacoes.csv"');
    
        $csv->output();
    
        exit;
    }
    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'produtos' => 'required|array',
            'produtos.*.nome' => 'required|string', 
            'produtos.*.quantidade' => 'required|integer|min:1', 
            'produtos.*.valor' => 'required|numeric|min:0', 
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
    
        $movimentacoes = Movimentacao::create($validatedData);
    
        return response()->json($movimentacoes, 201);
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