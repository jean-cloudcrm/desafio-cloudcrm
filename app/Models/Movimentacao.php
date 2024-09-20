<?php

namespace App\Models;
use App\Models\Cadastro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Movimentacao extends Model
{
    use HasFactory;
    protected $fillable=[
        'produtos',
        'formas_pagamento',
        'cadastro_id',
        'bloqueado'
    ];

    protected $table = 'movimentacoes';
   
    protected $casts = [
        'produtos' => 'array'
    ];

    public function cadastro()  
    {
        return $this->belongsTo(Cadastro::class, 'cadastro_id', 'id');
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}
