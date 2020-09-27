<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use App\Models\Nfsa;
use App\Models\Dam;
use App\Models\ItemNfsa;
use App\Http\Resources\NfsaResource;
// use App\Http\Resources\DamCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NfsaController extends Controller
{
    private $nfsa;
    private $dam;
    private $item;
    public function __construct(Nfsa $nfsa, Dam $dam, ItemNfsa $item)
    {
        $this->nfsa = $nfsa;
        $this->dam = $dam;
        $this->item = $item;
    }

    public function index(Request $request) {
        $nfsas = $this->nfsa;

        $contribuinte = $request->get('contribuinte') ?? '';

        if($request->has('sort'))
        {
            $sort = explode(',', $request->get('sort'));
            $nfsas = $nfsas->orderBy($sort[0], $sort[1]);
        }else{
            $nfsas = $nfsas->orderBy('id', 'DESC');
        }

        if($contribuinte != '')
        {
            $nfsas = $nfsas->whereHas('dam.contribuinte', function($query) use($contribuinte){
                $query->where('ci_contribuinte.nome', 'like', '%'.$contribuinte.'%')
                    ->orWhere('ci_contribuinte.doc', 'like', '%'.$contribuinte.'%');
            });
        }

        // VERIFICAÇÕES PARA FILTROS COMPLEXOS
        $id = $request->get('id') ?? '';
        $dataInicialFilter = $request->get('dataInicialFilter') ?? '';
        $dataFinalFilter = $request->get('dataFinalFilter') ?? '';
        $valorTotalFilter = $request->get('valorTotalFilter') ?? '';
        $namePrestadorFilter = $request->get('namePrestadorFilter') ?? '';
        $docPrestadorFilter = $request->get('docPrestadorFilter') ?? '';
        $nameTomadorFilter = $request->get('nameTomadorFilter') ?? '';
        $docTomadorFilter = $request->get('docTomadorFilter') ?? '';
        $situacaoFilter = $request->get('situacaoFilter') ?? '';
        $valorCalculo = $request->get('valorCalculo') ?? '';

        if($id != '')
        {
            $nfsas = $nfsas->where('id', $id);
        }
        if($valorCalculo != '')
        {
            $nfsas = $nfsas->where('valor_calculo', $valorCalculo);
        }

        if($dataInicialFilter != '' && $dataFinalFilter != '')
        {
            $nfsas = $nfsas->whereHas('dam', function($query) use($dataInicialFilter, $dataFinalFilter){
                $query->whereBetween('calculo', [$dataInicialFilter, $dataFinalFilter]);
            });
        }

        if($valorTotalFilter != '')
        {
            $value = explode(',', $valorTotalFilter);
            if($value[1] < 1000){

                $nfsas = $nfsas->whereHas('dam', function($query) use($value){
                    $query->whereBetween('valor_total', [$value[0], $value[1]]);
                });
            }
        }

        if($docPrestadorFilter != '')
        {
            $nfsas = $nfsas->whereHas('prestador', function($query) use($docPrestadorFilter){
                $query->where('ci_contribuinte.doc', 'like', $docPrestadorFilter.'%');
            });
        }
        if($namePrestadorFilter != '')
        {
            $nfsas = $nfsas->whereHas('prestador', function($query) use($namePrestadorFilter){
                $query->where('ci_contribuinte.nome', 'like', '%'.$namePrestadorFilter.'%');
            });
        }


        if($docPrestadorFilter != '')
        {
            $nfsas = $nfsas->whereHas('tomador', function($query) use($docPrestadorFilter){
                $query->where('ci_contribuinte.doc', 'like', $docPrestadorFilter.'%');
            });
        }
        if($nameTomadorFilter != '')
        {
            $nfsas = $nfsas->whereHas('tomador', function($query) use($nameTomadorFilter){
                $query->where('ci_contribuinte.nome', 'like', '%'.$nameTomadorFilter.'%');
            });
        }

        if($situacaoFilter != '')
        {
            switch ($situacaoFilter) {
                case 'pago':
                    $nfsas = $nfsas->whereHas('dam', function($query){
                        $query->where('pago', true)
                            ->where('status', true);
                    });
                    break;

                case 'vencer':
                    $nfsas = $nfsas->whereHas('dam', function($query){
                        $query->where('pago', false)
                            ->where('vencicmento', '>=', date('Y-m-d'))
                            ->where('status', true);
                    });
                    break;

                case 'inadimplente':
                    $nfsas = $nfsas->whereHas('dam', function($query){
                        $query->where('pago', false)
                            ->where('vencicmento', '<', date('Y-m-d'))
                            ->where('status', true);
                    });
                    break;

                case 'cancelado':
                    $nfsas = $nfsas->whereHas('dam', function($query){
                        $query->where('status', false);
                    });
                    break;
            }
        }

        $nfsas = $nfsas->with(['prestador', 'tomador', 'dam', 'itemsNfsa'])->paginate(30);
        return NfsaResource::collection($nfsas);
    }

    public function show($id) {
        $nfsas = $this->nfsa->find($id);
        return new NfsaResource($nfsas);
    }

    public function store(Request $request) {
        DB::beginTransaction();

        date_default_timezone_set('America/Sao_Paulo');
        $data = $request->all();

        //INSERIR PRIMEIRAMENTE O DAM PARA GERAR O RELACIONAMENTO COM A NFSA.
        try {
            $docOrigem = $data['dam']['docOrigem'] ?? '';
            $idContribuinte = $data['dam']['idContribuinte'] ?? '';
            $receita = $data['dam']['receita'] ?? '';
            $infoAdicionais = $data['dam']['infoAdicionais'] ?? '';
            $referencia = $data['dam']['referencia'] ?? '';
            $emissao = $data['dam']['emissao'] ?? '';
            $vencimento = $data['dam']['vencimento'] ?? '';
            $valorPrincipal = $data['dam']['valorPrincipal'] ?? 0;
            $taxaExp = $data['dam']['taxaExp'] ?? 0;
            $valorTotal = $data['dam']['valorTotal'] ?? 0;
            $juros = $data['dam']['juros'] ?? 0;
            $pago = $data['dam']['pago'] ?? false;
            $retido = $data['dam']['retido'] ?? false;
            $valorMulta = $data['dam']['valorMulta'] ?? 0;

            $dataDam = array(
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

            $dams = $this->dam->create($dataDam);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "falha ao inserir os dados do DAM, favor tentar mais tarde",
                "erro" => $th
            ], 501);
        }

        //INSERIR A NFSA PARA GERAR O RELACIONAMENTO DOS ITENS.
        try {
            $dataNFSA = array(
                'id_dam' => $dams->id,
                'id_prestador' => $data['nfsa']['idPrestador'],
                'id_tomador' => $data['nfsa']['idTomador'],
                'aliquota_iss' => $data['nfsa']['aliquotaISS'],
                'municipio' => $data['nfsa']['municipio'],
                'uf' => $data['nfsa']['uf'],
                'valor_nota' => $data['nfsa']['valorNota'],
                'valor_deducao' => $data['nfsa']['valorDeducao'],
                'valor_iss' => $data['nfsa']['valorISS'],
                'valor_calculo' => $data['nfsa']['valorCalculo'],
                'desconto_incodicional' => $data['nfsa']['descontoIncodicional'],
                'pis_percente' => $data['nfsa']['pisPercente'],
                'pis_valor' => $data['nfsa']['pisValor'],
                'confins_percente' => $data['nfsa']['confinsPercente'],
                'confins_valor' => $data['nfsa']['confinsValor'],
                'csll_percente' => $data['nfsa']['csllPercente'],
                'csll_valor' => $data['nfsa']['csllValor'],
                'inss_percente' => $data['nfsa']['inssPercente'],
                'inss_valor' => $data['nfsa']['inssValor'],
                'ir_percente' => $data['nfsa']['irPercente'],
                'ir_valor' => $data['nfsa']['irValor']
            );
            $nfsaInsertWithIdDam = array_merge($dataNFSA);
            $nfsas = $this->nfsa->create($nfsaInsertWithIdDam);
        } catch (\Illuminate\Database\QueryException $exception) {
            return response()->json([
                "message" => "falha ao inserir os dados da Nota Fiscal Avulsa de Serviço (NFSA), favor tentar mais tarde",
                "erro" => $exception
            ], 501);
        }

        //INSERIR OS ITENS DA NFSA
        try {
            $itemsNFSA = array();
            foreach ($data['items'] as $key => $value) {
                $nfsaItemInsertWithIdNFSA = array_merge(
                    array(
                        'descricao' => $value['descricao'],
                        'quantidade' => $value['quantidade'],
                        'valor' => $value['valor']
                    ),
                    ['id_nf' => $nfsas->id]
                );
                $itemNfsa = $this->item->create($nfsaItemInsertWithIdNFSA);
                array_push($itemsNFSA, $itemNfsa);
            }

            $nfsas = $nfsas->load(['prestador', 'tomador', 'dam', 'itemsNfsa']);
            DB::commit();

            return new NfsaResource($nfsas);

        } catch (\Illuminate\Database\QueryException $exception) {
            DB::rollBack();

            return response()->json([
                "message" => "falha ao inserir os items da Nota Fiscal Avulsa de Serviço (NFSA), favor tentar mais tarde",
                "erro" => $exception
            ], 501);
        }

    }

    public function update(Request $request, $id) {

        date_default_timezone_set('America/Sao_Paulo');
        $data = $request->all();
        // return response()->json($data);

        try {
            $idDAM = $data['nfsa']['dam']['id'] ?? '';
            $idContribuinte = $data['dam']['idPrestador'] ?? '';
            $docOrigem = $data['dam']['docOrigem'] ?? '';
            $infoAdicionais = $data['dam']['infoAdicionais'] ?? '';
            $referencia = $data['dam']['referencia'] ?? '';
            $vencimento = $data['dam']['vencimento'] ?? '';
            $valorPrincipal = $data['dam']['valorPrincipal'] ?? 0;
            $taxaExp = $data['dam']['taxaExp'] ?? 0;
            $valorTotal = $data['dam']['valorTotal'] ?? 0;
            $juros = $data['dam']['juros'] ?? 0;
            $valorMulta = $data['dam']['valorMulta'] ?? 0;

            $dataDam = array(
                'n_ref' => $docOrigem,
                'id_contribuinte' => $idContribuinte,
                'info_adicionais' => $infoAdicionais,
                'referencia' => $referencia,
                'vencicmento' => $vencimento,
                'valor_princapl' => $valorPrincipal,
                'valor_juros' => $juros,
                'valor_multa' => $valorMulta,
                'taxa_expedicao' => $taxaExp,
                'valor_total' => $valorTotal
            );
            $dams = $this->dam->find($idDAM)->update($dataDam);

        } catch (\Illuminate\Database\QueryException $exception) {
            return response()->json([
                "message" => "falha ao inserir os dados do DAM, favor tentar mais tarde",
                "erro" => $exception
            ], 501);
        }

        try {
            $dataNFSA = array(
                'id_prestador' => $data['dam']['idPrestador'],
                'id_tomador' => $data['dam']['idTomador'],
                'aliquota_iss' => $data['nfsa']['aliquotaISS'],
                'municipio' => $data['nfsa']['municipio'],
                'uf' => $data['nfsa']['uf'],
                'valor_nota' => $data['nfsa']['valorNota'],
                'valor_deducao' => $data['nfsa']['valorDeducao'],
                'valor_iss' => $data['nfsa']['valorISS'],
                'valor_calculo' => $data['nfsa']['valorCalculo'],
                'desconto_incodicional' => $data['nfsa']['descontoIncodicional'],
                'pis_percente' => $data['nfsa']['pisPercente'],
                'pis_valor' => $data['nfsa']['pisValor'],
                'confins_percente' => $data['nfsa']['confinsPercente'],
                'confins_valor' => $data['nfsa']['confinsValor'],
                'csll_percente' => $data['nfsa']['csllPercente'],
                'csll_valor' => $data['nfsa']['csllValor'],
                'inss_percente' => $data['nfsa']['inssPercente'],
                'inss_valor' => $data['nfsa']['inssValor'],
                'ir_percente' => $data['nfsa']['irPercente'],
                'ir_valor' => $data['nfsa']['irValor']
            );
            $nfsas = $this->nfsa->find($id)->update($dataNFSA);
        } catch (\Illuminate\Database\QueryException $exception) {
            return response()->json([
                "message" => "Não foi possível atualizar os dados da Nota Fiscal, favor tentar mais tarde",
                "erro" => $exception
            ], 501);
        }

        //INSERIR OS ITENS DA NFSA
        try {
            foreach ($data['items'] as $key => $value) {
                $nfsaItemInsertWithIdNFSA = array_merge($value, ['id_nf' => $id]);
                $idItem = $value['id'] ?? 0;
                if($idItem == 0){
                    $this->item->create($nfsaItemInsertWithIdNFSA);
                }else{
                    $this->item->find($value['id'])->update($nfsaItemInsertWithIdNFSA);
                }
            }

            $nfsaGet = $this->nfsa->find($id);

            return response()->json([
                "message" => "NFSA atualizada com sucesso!",
                "data" =>  'OK'// new NfsaResource($nfsaGet)
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                "message" => "falha ao inserir os items da Nota Fiscal Avulsa de Serviço (NFSA), favor tentar mais tarde",
                "erro" => $th
            ], 501);
        }
    }

    public function destroy() {

    }
}
