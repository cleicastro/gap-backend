<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabelaIR extends Model
{
    public $timestamps = false;
    protected $table = 'ci_tabela_ir';
    protected $fillable = [
        'id',
        'de',
        'ate',
        'aliquota',
        'deducao'
    ];
}
