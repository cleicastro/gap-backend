<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemNfsa extends Model
{
    public $timestamps = false;
    protected $table = 'ci_item_nfsa';
    protected $fillable = [
        'id',
        'id_nf',
        'descricao',
        'quantidade',
        'valor'
    ];

    public function nfsa()
    {
        return $this->belongsTo(Nsfa::class, 'id_nf', 'id');
    }
}
