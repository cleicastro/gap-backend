<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use DateTime;

class AlvaraFuncionamentoResource extends JsonResource
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
            'id_dam' => $this->id_dam,
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
            'atividade_principal' => $this->atividade_principal,
            'atividade_secundaria_I' => $this->atividade_secundaria_I,
            'atividade_secundaria_II' => $this->atividade_secundaria_II,
            'inscricao_municipal' => $this->inscricao_municipal,
            'nome_fantasia' => $this->nome_fantasia,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
            'cep' => $this->cep,
            'endereco' => $this->endereco,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro
        ];
    }
}
