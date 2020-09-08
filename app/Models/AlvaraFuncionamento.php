<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlvaraFuncionamento extends Model
{
    public $timestamps = false;
    protected $table = 'ci_alvara';
    protected $fillable = [
        'id_dam',
        'atividade_principal',
        'atividade_secundaria_I',
        'atividade_secundaria_II',
        'inscricao_municipal',
        'nome_fantasia',
        'cidade',
        'uf',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro'
    ];

    public function dam()
    {
        return $this->belongsTo(Dam::class, 'id_dam', 'id');
    }
}
