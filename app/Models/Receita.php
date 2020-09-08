<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receita extends Model
{
    public $timestamps = false;
    protected $table = 'ci_receita';
    protected $fillable = [
        'cod',
        'sigla',
        'descricao',
        'valor_fixo',
        'icon'
    ];

    public function dam()
    {
        return $this->hasMany(Contribuinte::class, 'receita', 'cod');
    }
}
