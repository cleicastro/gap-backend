<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use DateTime;

class NfsaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $interval = date_diff(new DateTime($this->dam->vencicmento), new DateTime('now'));
        $dias_vencimento =  0;

        if($this->dam->status == 0){
            $status = 'Cancelado';
        }elseif ($this->dam->pago == 1) {
            $status = 'Pago';
        }elseif($this->dam->pago == 0 &&  date('Y-m-d') > $this->dam->vencicmento){
            $status = 'Inadimplente';
            $dias_vencimento = $interval->format("%a");
        }else{
            $status = null;
            $dias_vencimento = $interval->format("%a") + 1;
        }

        return [
            'id' => $this->id,
            'items_nfsa' => $this->itemsNfsa,
            'dam' => array(
                'id' => $this->dam->id,
                'receita' => $this->dam->receita,
                'emissao' => $this->dam->emissao,
                'n_ref'=> $this->dam->n_ref,
                'info_adicionais'=> $this->dam->info_adicionais,
                'referencia'=> $this->dam->referencia,
                'calculo' => $this->dam->calculo,
                'vencimento' => $this->dam->vencicmento,
                'data_pagamento' => $this->dam->data_pagamento,
                'dias_vencimento' => $dias_vencimento,
                'valor_principal'=> $this->dam->valor_princapl,
                'valor_juros'=> $this->dam->valor_juros,
                'valor_multa'=> $this->dam->valor_multa,
                'taxa_expedicao'=> $this->dam->taxa_expedicao,
                'valor_total' => $this->dam->valor_total,
                'retido'=> $this->dam->retido,
                'pago'=> $this->dam->pago,
                'status' => $status,
                'contribuinte' => $this->dam->contribuinte
            ),
            'prestador' => $this->prestador,
            'tomador' => $this->tomador,
            'aliquota_iss' => $this->aliquota_iss,
            'municipio' => $this->municipio,
            'uf' => $this->uf,
            'valor_nota' => $this->valor_nota,
            'valor_deducao' => $this->valor_deducao,
            'valor_iss' => $this->valor_iss,
            'valor_calculo' => $this->valor_calculo,
            'desconto_incodicional' => $this->desconto_incodicional,
            'pis_percente' => $this->pis_percente,
            'pis_valor' => $this->pis_valor,
            'confins_percente' => $this->confins_percente,
            'confins_valor' => $this->confins_valor,
            'csll_percente' => $this->csll_percente,
            'csll_valor' => $this->csll_valor,
            'inss_percente' => $this->inss_percente,
            'inss_valor' => $this->inss_valor,
            'ir_percente' => $this->ir_percente,
            'ir_valor' => $this->ir_valor
        ];
    }
}
