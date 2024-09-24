<?php

namespace App\Models;
use App\Models\Cadastro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Movimentacao extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
