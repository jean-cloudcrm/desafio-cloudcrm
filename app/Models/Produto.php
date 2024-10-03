<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Bom, apenas cuidar com a identação do código
class Produto extends Model
{
    use HasFactory;

    protected $fillable=[
        'nome',
        'quantidade',
        'valor'
    ];

    public function movimentacoes()
{
    return $this->belongsToMany(Movimentacao::class, 'movimentacao_produto', 'produto_id', 'movimentacao_id');
}
}
