<?php

namespace App\Http\Controllers\Api;

use App\Models\Dam;
use App\Http\Resources\DamResource;
use App\Http\Resources\DamCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DamController extends Controller
{
    private $dam;
    public function __construct(Dam $dam)
    {
        $this->dam = $dam;
    }

    public function index(Request $request)
    {
        $dams = $this->dam;

        $id = $request->get('id') ?? '';
        $emissao = $request->get('emissao') ?? '';
        $vencimento = $request->get('vencimento') ?? '';
        $valorTotal = $request->get('valorTotal') ?? '';
        $contribuinte = $request->get('contribuinte') ?? '';
        $receita = $request->get('receita') ?? '';
        $contribuinteInadimplente = $request->get('contribuinteInadimplente') ?? '';

        if($request->has('sort'))
        {
            $sort = explode(',', $request->get('sort'));
            $dams = $dams->orderBy($sort[0], $sort[1]);
        }else{
            $dams = $dams->orderBy('id', 'DESC');
        }

        if($id != '')
        {
            $dams = $dams->where('id', $id);
        }
        if($emissao != '')
        {
            $dams = $dams->where('emissao', 'like', $emissao.'%');
        }
        if($vencimento != '')
        {
            $dams = $dams->where('vencicmento', $vencimento);
        }
        if($valorTotal != '')
        {
            $dams = $dams->where('valor_total', 'like', $valorTotal.'%');
        }
        if($contribuinte != '')
        {
            $dams = $dams->whereHas('contribuinte', function($query) use($contribuinte){
                $query->where('ci_contribuinte.nome', 'like', '%'.$contribuinte.'%')
                    ->orWhere('ci_contribuinte.doc', 'like', '%'.$contribuinte.'%');
            });
        }
        if($receita != '')
        {
            $dams = $dams->whereHas('receitaCod', function($query) use($receita){
                $query->where('ci_receita.descricao', 'like', '%'.$receita.'%')
                    ->orWhere('ci_receita.cod', 'like', '%'.$receita.'%');
            });
        }

        // VERIFICAÇÕES PARA FILTROS COMPLEXOS
        $receitaFilter = $request->get('receitaFilter') ?? '';
        $dataInicialFilter = $request->get('dataInicialFilter') ?? '';
        $dataFinalFilter = $request->get('dataFinalFilter') ?? '';
        $valorTotalFilter = $request->get('valorTotalFilter') ?? '';
        $docContribuinteFilter = $request->get('docContribuinteFilter') ?? '';
        $nameContribuinteFilter = $request->get('nameContribuinteFilter') ?? '';
        $situacaoFilter = $request->get('situacaoFilter') ?? '';

        if($receitaFilter != '')
        {
            $dams = $dams->whereHas('receitaCod', function($query) use($receitaFilter){
                $query->whereIn('ci_receita.descricao', explode(',', $receitaFilter));
            });
        }

        if($dataInicialFilter != '' && $dataFinalFilter != '')
        {
            $dams = $dams->whereBetween('calculo', [$dataInicialFilter, $dataFinalFilter]);
        }

        if($valorTotalFilter != '')
        {
            $value = explode(',', $valorTotalFilter);
            if($value[1] < 1000){
                $dams = $dams->whereBetween('valor_total', [$value[0], $value[1]]);
            }
        }

        if($docContribuinteFilter != '')
        {
            $dams = $dams->whereHas('contribuinte', function($query) use($docContribuinteFilter){
                $query->where('ci_contribuinte.doc', 'like', $docContribuinteFilter.'%');
            });
        }

        if($nameContribuinteFilter != '')
        {
            $dams = $dams->whereHas('contribuinte', function($query) use($nameContribuinteFilter){
                $query->where('ci_contribuinte.nome', 'like', '%'.$nameContribuinteFilter.'%');
            });
        }

        if($contribuinteInadimplente != '')
        {
            $dams = $dams->where('id_contribuinte', $contribuinteInadimplente)
            ->where('pago', false)
            ->where('vencicmento', '<', date('Y-m-d'))
            ->where('status', true);
        }

        if($situacaoFilter != '')
        {
            switch ($situacaoFilter) {
                case 'pago':
                    $dams = $dams->where('pago', true)
                        ->where('status', true);
                    break;

                case 'vencer':
                    $dams = $dams->where('pago', false)
                        ->where('vencicmento', '>=', date('Y-m-d'))
                        ->where('status', true);
                    break;

                case 'inadimplente':
                    $dams = $dams->where('pago', false)
                        ->where('vencicmento', '<', date('Y-m-d'))
                        ->where('status', true);
                    break;

                case 'cancelado':
                    $dams = $dams->where('status', false);
                    break;
            }
        }

        $dams = $dams->with(['contribuinte', 'receitaCod'])->paginate(30);
        return DamResource::collection($dams);
    }

    public function show($id)
    {
        $dam = $this->dam->find($id);
        return new DamResource($dam);
    }

    public function store(Request $request)
    {
        date_default_timezone_set('America/Sao_Paulo');

        $docOrigem = $request->get('docOrigem') ?? '';
        $idContribuinte = $request->get('idContribuinte') ?? '';
        $receita = $request->get('receita') ?? '';
        $infoAdicionais = $request->get('infoAdicionais') ?? '';
        $referencia = $request->get('referencia') ?? '';
        $vencimento = $request->get('vencimento') ?? '';
        $valorPrincipal = $request->get('valorPrincipal') ?? 0;
        $taxaExp = $request->get('taxaExp') ?? 0;
        $valorTotal = $request->get('valorTotal') ?? 0;
        $juros = $request->get('juros') ?? 0;
        $pago = $request->get('pago') ?? false;
        $retido = $request->get('retido') ?? false;
        $valorMulta = $request->get('valorMulta') ?? 0;

        $data = array(
            'n_ref' => $docOrigem,
            'id_contribuinte' => $idContribuinte,
            'receita' => $receita,
            'info_adicionais' => $infoAdicionais,
            'referencia' => $referencia,
            'calculo' => date('Y-m-d'),
            'vencicmento' => $vencimento,
            'data_pagamento' => null,
            'emissao' => date('Y-m-d H:i:s'),
            'retido' => true,
            'pago' => false,
            'valor_princapl' => $valorPrincipal,
            'valor_juros' => $juros,
            'valor_multa' => $valorMulta,
            'taxa_expedicao' => $taxaExp,
            'valor_total' => $valorTotal,
            'status' => true
        );

        //return response()->json($data);
        $dams = $this->dam->create($data);
        $dams = $dams->load(['contribuinte', 'receitaCod']);
        return new DamResource($dams);
    }

    public function update(Request $request, $id)
    {

        if($request->has('status') || $request->has('pago'))
        {
            $data = $request->all();
        } else {
            $docOrigem = $request->get('docOrigem') ?? '';
            $idContribuinte = $request->get('idContribuinte') ?? '';
            $receita = $request->get('receita') ?? '';
            $infoAdicionais = $request->get('infoAdicionais') ?? '';
            $referencia = $request->get('referencia') ?? '';
            $vencimento = $request->get('vencimento') ?? '';
            $valorPrincipal = $request->get('valorPrincipal') ?? '';
            $taxaExp = $request->get('taxaExp') ?? '';
            $valorTotal = $request->get('valorTotal') ?? '';
            $juros = $request->get('juros') ?? '';
            $pago = $request->get('pago') ?? '';
            $retido = $request->get('retido') ?? '';
            $valorMulta = $request->get('valorMulta') ?? '';

            $data = array(
                'n_ref' => $docOrigem,
                'id_contribuinte' => $idContribuinte,
                'receita' => $receita,
                'info_adicionais' => $infoAdicionais,
                'referencia' => $referencia,
                'vencicmento' => $vencimento,
                'retido' => true,
                'valor_princapl' => $valorPrincipal,
                'valor_juros' => $juros,
                'valor_multa' => $valorMulta,
                'taxa_expedicao' => $taxaExp,
                'valor_total' => $valorTotal
            );
        }
        $dam = $this->dam->find($id)->update($data);
        return response()->json([
            "message" => "atualizado com sucesso"
        ], 200);
    }

    public function destroy($id)
    {
        $dam = $this->dam->find($id)->delete();
        return response()->json(['data' => ['msg' => 'Dam removido com sucesso']]);
    }
}
