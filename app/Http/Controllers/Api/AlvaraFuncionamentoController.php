<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use App\Models\AlvaraFuncionamento;
use App\Models\Dam;
use App\Http\Resources\AlvaraFuncionamentoResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AlvaraFuncionamentoController extends Controller
{
    private $alvara;
    private $dam;
    public function __construct(AlvaraFuncionamento $alvara, Dam $dam)
    {
        $this->alvara = $alvara;
        $this->dam = $dam;
    }

    public function index(Request $request) {
        $alvaras = $this->alvara;

        $contribuinte = $request->get('contribuinte') ?? '';
        $contribuinteInadimplente = $request->get('contribuinteInadimplente') ?? '';

        if($request->has('sort'))
        {
            $sort = explode(',', $request->get('sort'));
            $alvaras = $alvaras->orderBy('id_dam', $sort[1]);
        }else{
            $alvaras = $alvaras->orderBy('id_dam', 'DESC');
        }


        if($contribuinte != '')
        {
            $alvaras = $alvaras->whereHas('dam.contribuinte', function($query) use($contribuinte){
                $query->where('ci_contribuinte.nome', 'like', '%'.$contribuinte.'%')
                    ->orWhere('ci_contribuinte.doc', 'like', '%'.$contribuinte.'%');
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
            $alvaras = $alvaras->where('dam.receita', $receitaFilter);
        }

        if($dataInicialFilter != '' && $dataFinalFilter != '')
        {
            $alvaras = $alvaras->whereHas('dam', function($query) use($dataInicialFilter, $dataFinalFilter){
                $query->whereBetween('calculo', [$dataInicialFilter, $dataFinalFilter]);
            });
        }

        if($valorTotalFilter != '')
        {
            $value = explode(',', $valorTotalFilter);
            if($value[1] < 1000){

                $alvaras = $alvaras->whereHas('dam', function($query) use($value){
                    $query->whereBetween('valor_total', [$value[0], $value[1]]);
                });
            }
        }

        if($docContribuinteFilter != '')
        {
            $alvaras = $alvaras->whereHas('dam.contribuinte', function($query) use($docContribuinteFilter){
                $query->where('ci_contribuinte.doc', 'like', $docContribuinteFilter.'%');
            });
        }

        if($nameContribuinteFilter != '')
        {
            $alvaras = $alvaras->whereHas('dam.contribuinte', function($query) use($nameContribuinteFilter){
                $query->where('ci_contribuinte.nome', 'like', '%'.$nameContribuinteFilter.'%');
            });
        }

        if($contribuinteInadimplente != '')
        {
            $alvaras = $alvaras->whereHas('dam', function($query) use($contribuinteInadimplente){
                $query->where('id_contribuinte', $contribuinteInadimplente)
                ->where('pago', false)
                ->where('vencicmento', '<', date('Y-m-d'))
                ->where('status', true);
            });
        }

        if($situacaoFilter != '')
        {
            switch ($situacaoFilter) {
                case 'pago':
                    $alvaras = $alvaras->whereHas('dam', function($query){
                        $query->where('pago', true)
                        ->where('status', true);
                    });
                    break;

                case 'vencer':
                    $alvaras = $alvaras->whereHas('dam', function($query){
                        $query->where('pago', false)
                        ->where('vencicmento', '>=', date('Y-m-d'))
                        ->where('status', true);
                    });
                    break;

                case 'inadimplente':
                    $alvaras = $alvaras->whereHas('dam', function($query){
                        $query->where('pago', false)
                        ->where('vencicmento', '<', date('Y-m-d'))
                        ->where('status', true);
                    });
                    break;

                case 'cancelado':
                    $alvaras = $alvaras->whereHas('dam', function($query){
                        $query->where('status', false);
                    });
                    break;
            }
        }

        $alvaras = $alvaras->with(['dam', 'dam.contribuinte'])->paginate(30);
        return AlvaraFuncionamentoResource::collection($alvaras);
    }

    public function show($id) {
        $alvara = $this->alvara;
        $alvara = $alvara->whereHas('dam', function($query)  use($id){
            $query->where('id',  $id);
        });
        $alvara = $alvara->with(['dam', 'dam.contribuinte'])->get()[0];

        return response()->json(['data' => $alvara]);
    }

    public function store(Request $request) {
        DB::beginTransaction();

        date_default_timezone_set('America/Sao_Paulo');
        $data = $request->all();

        //INSERIR PRIMEIRAMENTE O DAM PARA GERAR O RELACIONAMENTO COM o alvará.
        try {
            $docOrigem = $data['docOrigem'] ?? '';
            $idContribuinte = $data['idContribuinte'] ?? '';
            $receita = $data['receita'] ?? '';
            $infoAdicionais = $data['infoAdicionais'] ?? '';
            $referencia = $data['referencia'] ?? '';
            $emissao = $data['emissao'] ?? '';
            $vencimento = $data['vencimento'] ?? '';
            $valorPrincipal = $data['valorPrincipal'] ?? 0;
            $taxaExp = $data['taxaExp'] ?? 0;
            $valorTotal = $data['valorTotal'] ?? 0;
            $juros = $data['juros'] ?? 0;
            $pago = $data['pago'] ?? false;
            $retido = $data['retido'] ?? false;
            $valorMulta = $data['valorMulta'] ?? 0;

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

            $alvara = $this->dam->create($dataDam);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "falha ao inserir os dados do DAM, favor tentar mais tarde",
                "erro" => $th
            ], 501);
        } catch (Exception $e) {
            return response()->json([
                "message" => "falha ao inserir os dados do DAM, favor tentar mais tarde",
                "erro" => $e
            ], 501);
        }
        //INSERIR A alvara PARA GERAR O RELACIONAMENTO DOS ITENS.
        try {
            $dataAlvara = array(
                'id_dam' => $alvara->id,
                'atividade_principal' =>  $data['atividadePrincipal'],
                'atividade_secundaria_I' =>  $data['atividadeSecundariaI'],
                'atividade_secundaria_II' =>  $data['atividadeSecundariaII'],
                'inscricao_municipal' =>  $data['inscricaoMunicipal'],
                'nome_fantasia' =>  $data['nomeFantasia'],
                'cidade' =>  $data['cidade'],
                'uf' =>  $data['uf'],
                'cep' =>  $data['cep'],
                'endereco' =>  $data['endereco'],
                'numero' =>  $data['numero'],
                'complemento' =>  $data['complemento'],
                'bairro' =>  $data['bairro']
            );
            $alvaraInsertWithIdDam = array_merge($dataAlvara);
            $alvara = $this->alvara->create($alvaraInsertWithIdDam);

            $alvara = $alvara->load(['dam', 'dam.contribuinte']);
            DB::commit();

            return new AlvaraFuncionamentoResource($alvara);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                "message" => "falha ao inserir os dados do DAM, favor tentar mais tarde",
                "erro" => $e
            ], 501);
        }
    }

    public function update(Request $request, $id) {

        if($request->has('status') || $request->has('pago'))
        {
            $data = $request->all();
            $dam = $this->dam->find($id)->update($data);
            return response()->json([
                "message" => "atualizado com sucesso"
            ], 200);
        }

        date_default_timezone_set('America/Sao_Paulo');
        $data = $request->all();

        try {
            $idDAM = $data['dam']['id'] ?? '';
            $docOrigem = $data['docOrigem'] ?? '';
            $idContribuinte = $data['idContribuinte'] ?? '';
            $receita = $data['receita'] ?? '';
            $infoAdicionais = $data['infoAdicionais'] ?? '';
            $referencia = $data['referencia'] ?? '';
            $emissao = $data['emissao'] ?? '';
            $vencimento = $data['vencimento'] ?? '';
            $valorPrincipal = $data['valorPrincipal'] ?? 0;
            $taxaExp = $data['taxaExp'] ?? 0;
            $valorTotal = $data['valorTotal'] ?? 0;
            $juros = $data['juros'] ?? 0;
            $valorMulta = $data['valorMulta'] ?? 0;

            $dataDam = array(
                'n_ref' => $docOrigem,
                'id_contribuinte' => $idContribuinte,
                'receita' => $receita,
                'info_adicionais' => $infoAdicionais,
                'referencia' => $referencia,
                'vencicmento' => $vencimento,
                'data_pagamento' => null,
                'valor_princapl' => $valorPrincipal,
                'valor_juros' => $juros,
                'valor_multa' => $valorMulta,
                'taxa_expedicao' => $taxaExp,
                'valor_total' => $valorTotal
            );
            $alvaras = $this->dam->find($idDAM)->update($dataDam);
        } catch (Exception $e) {
            return response()->json([
                "message" => "falha ao inserir os dados do DAM, favor tentar mais tarde",
                "erro" => $e
            ], 501);
        }

        try {
            $dataAlvara = array(
                'atividade_principal' =>  $data['atividadePrincipal'],
                'atividade_secundaria_I' =>  $data['atividadeSecundariaI'],
                'atividade_secundaria_II' =>  $data['atividadeSecundariaII'],
                'inscricao_municipal' =>  $data['inscricaoMunicipal'],
                'nome_fantasia' =>  $data['nomeFantasia'],
                'cidade' =>  $data['cidade'],
                'uf' =>  $data['uf'],
                'cep' =>  $data['cep'],
                'endereco' =>  $data['endereco'],
                'numero' =>  $data['numero'],
                'complemento' =>  $data['complemento'],
                'bairro' =>  $data['bairro']
            );
            $alvaraInsertWithIdDam = array_merge($dataAlvara);
            $alvaras = $this->alvara->where('id_dam', $data['id_dam'])->update($alvaraInsertWithIdDam);

            return response()->json([
                "message" => "atualizado com sucesso"
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "message" => "falha ao inserir os dados da Nota Fiscal Avulsa de Serviço (alvara), favor tentar mais tarde",
                "erro" => $e
            ], 501);
        }
    }

    public function destroy() {

    }
}
