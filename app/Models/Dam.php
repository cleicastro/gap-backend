<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dam extends Model
{
    public $timestamps = false;
    protected $table = 'ci_dam';
    protected $fillable = [
        'id',
        'n_ref',
        'id_contribuinte',
        'receita',
        'info_adicionais',
        'referencia',
        'calculo',
        'vencicmento',
        'data_pagamento',
        'emissao',
        'retido',
        'pago',
        'valor_princapl',
        'valor_juros',
        'valor_multa',
        'taxa_expedicao',
        'valor_total',
        'status'
    ];

    public function contribuinte()
    {
        return $this->belongsTo(Contribuinte::class, 'id_contribuinte', 'id');
    }

    public function receitaCod()
    {
        return $this->belongsTo(Receita::class, 'receita', 'cod');
    }

    public function nfsa()
    {
        return $this->hasMany(Nfsa::class, 'id_dam', 'id');
    }

    public function alvaraFuncionamento()
    {
        return $this->hasMany(AlvaraFuncionamento::class, 'id_dam', 'id');
    }
}
