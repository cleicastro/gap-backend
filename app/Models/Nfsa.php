<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nfsa extends Model
{
    public $timestamps = false;
    protected $table = 'ci_nfsa';
    protected $fillable = [
        'id',
        'id_dam',
        'cod_nfsa',
        'id_prestador',
        'id_tomador',
        'aliquota_iss',
        'municipio',
        'uf',
        'valor_nota',
        'valor_deducao',
        'valor_iss',
        'valor_calculo',
        'desconto_incodicional',
        'pis_percente',
        'pis_valor',
        'confins_percente',
        'confins_valor',
        'csll_percente',
        'csll_valor',
        'inss_percente',
        'inss_valor',
        'ir_percente',
        'ir_valor'
    ];

    public function prestador()
    {
        return $this->belongsTo(Contribuinte::class, 'id_prestador', 'id');
    }

    public function tomador()
    {
        return $this->belongsTo(Contribuinte::class, 'id_tomador', 'id');
    }

    public function dam()
    {
        return $this->belongsTo(Dam::class, 'id_dam', 'id');
    }

    public function itemsNfsa()
    {
        return $this->hasMany(ItemNfsa::class, 'id_nf', 'id');
    }
}
