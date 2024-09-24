<?php

namespace App\Http\Controllers;
use App\Models\Movimentacao;
use App\Models\Cadastro;
use League\Csv\Writer;
use League\Csv\CharsetConverter;

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
    // Obtém as movimentações com a relação de cadastro
    $movimentacoes = Movimentacao::with('cadastro')->get();

    // Cria o objeto CSV
    $csv = Writer::createFromFileObject(new \SplTempFileObject());

    // Verifica se o suporte a stream filter está ativado para escrita
    if ($csv->supportsStreamFilterOnWrite()) {
        // Adiciona o filtro de conversão de charset
        $csv->addStreamFilter(CharsetConverter::addTo('utf-8', 'iso-8859-1'));
    }

    // Define o cabeçalho do CSV
    $csv->insertOne(['nome_produto', 'quantidade_produto', 'valor_produto', 'formas_pagamento', 'cadastro_nome', 'cadastro_email', 'cadastro_birthday', 'bloqueado']);

    // Itera sobre cada movimentação
    foreach ($movimentacoes as $movimentacao) {
        // Decodifica os produtos JSON, adicionando um fallback para um array vazio se a decodificação falhar
        $produtos = is_array($movimentacao->produtos) ? $movimentacao->produtos : json_decode($movimentacao->produtos, true);

        // Verifica se a decodificação foi bem-sucedida
        if (!is_array($produtos)) {
            $produtos = [];
        }

        // Para cada produto, insira uma nova linha no CSV
        foreach ($produtos as $produto) {
            // Garante que o produto tenha as chaves necessárias (tratamento de erro)
            $nomeProduto = $produto['nome'] ?? 'N/A';  // Se não houver chave 'nome', usa 'N/A'
            $quantidadeProduto = $produto['quantidade'] ?? '0'; // Se não houver chave 'quantidade', usa '0'
            $valorProduto = $produto['valor'] ?? '0.00'; // Se não houver chave 'valor', usa '0.00'

            // Transforma a coluna 'bloqueado'
            $bloqueado = $movimentacao->bloqueado ? 'SIM' : 'NÃO';

            // Verifica se a relação cadastro existe
            $cadastroNome = $movimentacao->cadastro->nome ?? 'N/A';
            $cadastroEmail = $movimentacao->cadastro->email ?? 'N/A';
            $cadastroBirthday = $movimentacao->cadastro->birthday ?? 'N/A';

            // Adiciona os dados correspondentes a cada cabeçalho
            $csv->insertOne([
                $nomeProduto,                 // nome_produto
                $quantidadeProduto,           // quantidade_produto
                $valorProduto,                // valor_produto
                $movimentacao->formas_pagamento, // formas_pagamento
                $cadastroNome,                // cadastro_nome
                $cadastroEmail,               // cadastro_email
                $cadastroBirthday,            // cadastro_birthday
                $bloqueado                    // bloqueado
            ]);
        }
    }

    // Define o tipo de conteúdo e o nome do arquivo
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="movimentacoes.csv"');

    // Saída do CSV
    $csv->output();

    // Interrompe a execução para garantir que o CSV seja baixado corretamente
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