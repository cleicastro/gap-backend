<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CadastroAlvaraFuncionamento extends Model
{
    public $timestamps = false;
    protected $table = 'ci_cad_alvara';
    protected $fillable = [
        'id_contribuinte',
        'atividade_principal',
        'atividade_secundaria_I',
        'atividade_secundaria_II',
        'inscricao_municipal'
    ];

    public function contribuinte()
    {
        return $this->hasManys(Contribuinte::class, 'id_contribuinte', 'id');
    }
}
