<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contribuinte extends Model
{
    public $timestamps = false;
    protected $table = 'ci_contribuinte';
    protected $fillable = [
        'id',
        'tipo',
        'doc',
        'doc_estadual',
        'doc_emissao',
        'doc_orgao',
        'nome',
        'nome_fantasia',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'cep',
        'uf',
        'telefone',
        'email',
        'banco',
        'tipoConta',
        'variacao',
        'agencia',
        'conta'
    ];

    public function dam()
    {
        return $this->hasMany(Dam::class, 'id_contribuinte', 'id');
    }

    public function cadAlvara()
    {
        return $this->belongsTo(CadastroAlvaraFuncionamento::class, 'id', 'id_contribuinte');
    }
}
