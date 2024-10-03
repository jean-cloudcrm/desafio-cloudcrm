<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
//Ok
class Cadastro extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        "nome",
        "email",
        "birthday",
    ];

    public function movimentacao()
    {
        return $this->hasMany(Movimentacao::class, 'cadastro_id', 'id');
    }
    // Entendi a ideia aqui, mas poderiamos usar a propriedade $casts e incluir o campo que desejar parsear;
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getBirthdayAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
