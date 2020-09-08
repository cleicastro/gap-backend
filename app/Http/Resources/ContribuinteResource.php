<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContribuinteResource extends JsonResource
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
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'doc' => $this->doc,
            'nome' => $this->nome,
            'docEstadual' => $this->doc_estadual,
            'nomeFantasia' => $this->nome_fantasia,
            'docEmissao' => $this->doc_emissao,
            'docOrgao' => $this->doc_orgao,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'cep' => $this->cep,
            'uf' => $this->uf,
            'cidade' => $this->cidade,
            'endereco' => $this->endereco,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'banco' => $this->banco,
            'agencia' => $this->agencia,
            'conta' => $this->conta,
            'variacao' => $this->variacao,
            'tipoConta' => $this->tipoConta,
            'cadAlvara' => $this->cadAlvara
        ];
    }
}
