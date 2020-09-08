<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use DateTime;

class DamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        date_default_timezone_set('America/Sao_Paulo');
        $interval = date_diff(new DateTime($this->vencicmento), new DateTime('now'));
        $dias_vencimento =  0;

        if($this->status == 0){
            $status = 'Cancelado';
        }elseif ($this->pago == 1) {
            $status = 'Pago';
        }elseif($this->pago == 0 &&  date('Y-m-d') > $this->vencicmento){
            $status = 'Inadimplente';
            $dias_vencimento = $interval->format("%a");
        }else{
            $status = null;
            $dias_vencimento = $interval->format("%a") + 1;
        }
        return [
            'id' => $this->id,
            'emissao' => $this->emissao,
            'n_ref'=> $this->n_ref,
            'info_adicionais'=> $this->info_adicionais,
            'referencia'=> $this->referencia,
            'calculo' => $this->calculo,
            'vencimento' => $this->vencicmento,
            'data_pagamento' => $this->data_pagamento,
            'dias_vencimento' => $dias_vencimento,
            'valor_principal'=> $this->valor_princapl,
            'valor_juros'=> $this->valor_juros,
            'valor_multa'=> $this->valor_multa,
            'taxa_expedicao'=> $this->taxa_expedicao,
            'valor_total' => $this->valor_total,
            'retido'=> $this->retido,
            'pago'=> $this->pago,
            'status' => $status,
            'contribuinte' => $this->contribuinte,
            'receita' => $this->receitaCod
        ];
    }
}
