<?php

namespace App\Http\Controllers\Api;

use App\Models\Contribuinte;
use App\Models\CadastroAlvaraFuncionamento;
use App\Http\Resources\ContribuinteResource;
use App\Http\Resources\ContribuinteCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContribuinteController extends Controller
{

    private $contribuinte;
    private $cadAlvara;
    public function __construct(Contribuinte $contribuinte, CadastroAlvaraFuncionamento $cadAlvara)
    {
        $this->contribuinte = $contribuinte;
        $this->cadAlvara = $cadAlvara;
    }

    public function index(Request $request)
    {
        $contribuintes = $this->contribuinte;

        //LIMIT
        $limit = $request->get('limit') ?? 30;

        //ORDENAÇÃO
        if($request->has('sort'))
        {
            $sort = explode(',', $request->get('sort'));

            $contribuintes = $sort[0] == 'enderecoCidade' ?
            $contribuintes->orderByRaw("cidade, bairro, endereco {$sort[1]}")
            : $contribuintes->orderBy($sort[0], $sort[1]);

        }else{
            $contribuintes = $contribuintes->orderBy('id', 'DESC');
        }

        $id = $request->get('id') ?? '';
        $doc = $request->get('doc') ?? '';
        $nome = $request->get('nome') ?? '';
        $tipo = $request->get('tipo') ?? '';
        $contribuinte = $request->get('contribuinte') ?? '';
        $enderecoCidade = $request->get('enderecoCidade') ?? '';

        if($id != '')
        {
            $contribuintes = $contribuintes->where('id', $id);
        }
        if($doc != '')
        {
            $contribuintes = $contribuintes->where('doc', 'like', $doc.'%');
        }
        if($nome != '')
        {
            $contribuintes = $contribuintes->where('nome', 'like', "%{$nome}%");
        }
        if($tipo != '')
        {
            $contribuintes = $contribuintes->where('tipo', $tipo);
        }

        if($contribuinte != '')
        {
            $contribuintes = $contribuintes->where('doc', 'like', $contribuinte.'%')
            ->orWhere('nome', 'like', $contribuinte.'%');
        }

        if($enderecoCidade != '')
        {
            $contribuintes = $contribuintes->where('cidade', 'like', $enderecoCidade.'%')
            ->orWhere('bairro', 'like', $enderecoCidade.'%')
            ->orWhere('endereco', 'like', $enderecoCidade.'%');
        }

        $contribuintes = $contribuintes->with('cadAlvara')->paginate($limit);
        return contribuinteResource::collection($contribuintes);
    }

    public function show($id)
    {
        $contribuintes = $this->contribuinte->with('cadAlvara')->find($id);
        return new contribuinteResource($contribuintes);
    }

    public function store(Request $request)
    {
        try {
            $data = array();

            $tipo = $request->get('tipo') ?? '';
            $doc = $request->get('doc') ?? '';
            $nome = $request->get('nome') ?? '';
            $docEstadual = $request->get('docEstadual') ?? '';
            $docEmissao = $request->get('docEmissao') ?? '';
            $docOrgao = $request->get('docOrgao') ?? '';
            $telefone = $request->get('telefone') ?? '';
            $email = $request->get('email') ?? '';
            $cep = $request->get('cep') ?? '';
            $uf = $request->get('uf') ?? '';
            $cidade = $request->get('cidade') ?? '';
            $endereco = $request->get('endereco') ?? '';
            $numero = $request->get('numero') ?? '';
            $complemento = $request->get('complemento') ?? '';
            $bairro = $request->get('bairro') ?? '';
            $banco = $request->get('banco') ?? '';
            $agencia = $request->get('agencia') ?? '';
            $conta = $request->get('conta') ?? '';
            $variacao = $request->get('variacao') ?? 0;
            $tipoConta = $request->get('tipoConta') ?? '';

            $inscricaoMunicipal = $request->get('inscricaoMunicipal') ?? '';
            $nomeFantasia = $request->get('nomeFantasia') ?? '';
            $atividadePrincipal = $request->get('atividadePrincipal') ?? '';
            $atividadeSecundariaI = $request->get('atividadeSecundariaI') ?? '';
            $atividadeSecundariaII = $request->get('atividadeSecundariaII') ?? '';

            $data = array(
                'tipo' => $tipo,
                'doc' => $doc,
                'nome' => $nome,
                'doc_estadual' => $docEstadual,
                'doc_emissao' => $docEmissao ? implode('-', array_reverse(explode('/', $docEmissao))) : NULL,
                'doc_orgao' => $docOrgao,
                'telefone' => $telefone,
                'email' => $email,
                'cep' => $cep,
                'uf' => $uf,
                'cidade' => $cidade,
                'endereco' => $endereco,
                'numero' => $numero,
                'complemento' => $complemento,
                'bairro' => $bairro,
                'banco' => $banco,
                'agencia' => $agencia,
                'conta' => $conta,
                'variacao' => $variacao,
                'tipoConta' => $tipoConta
            );
            $contribuinte = $this->contribuinte->create($data);

            if($inscricaoMunicipal != '')
            {
                $dataCadAlvara = array(
                    'id_contribuinte' => $contribuinte->id,
                    'atividade_principal' => $atividadePrincipal,
                    'atividade_secundaria_I' => $atividadeSecundariaI,
                    'atividade_secundaria_II' => $atividadeSecundariaII,
                    'inscricao_municipal' => $inscricaoMunicipal
                );
                $cadAlvara = $this->cadAlvara->create($data);
            }

            return new contribuinteResource($contribuinte);

        } catch (\Throwable $th) {
            return response()->json([
                "message" => "falha ao inserir os dados do contribuinte, favor tentar mais tarde",
                "erro" => $th
            ], 501);
        }
    }

    public function update(Request $request, $id)
    {
        $data = array();

        $tipo = $request->get('tipo') ?? '';
        $doc = $request->get('doc') ?? '';
        $nome = $request->get('nome') ?? '';
        $docEstadual = $request->get('docEstadual') ?? '';
        $docEmissao = $request->get('docEmissao') ?? '';
        $docOrgao = $request->get('docOrgao') ?? '';
        $telefone = $request->get('telefone') ?? '';
        $email = $request->get('email') ?? '';
        $cep = $request->get('cep') ?? '';
        $uf = $request->get('uf') ?? '';
        $cidade = $request->get('cidade') ?? '';
        $endereco = $request->get('endereco') ?? '';
        $numero = $request->get('numero') ?? '';
        $complemento = $request->get('complemento') ?? '';
        $bairro = $request->get('bairro') ?? '';
        $banco = $request->get('banco') ?? '';
        $agencia = $request->get('agencia') ?? '';
        $conta = $request->get('conta') ?? '';
        $variacao = $request->get('variacao') ?? 0;
        $tipoConta = $request->get('tipoConta') ?? '';

        $inscricaoMunicipal = $request->get('inscricaoMunicipal') ?? '';
        $nomeFantasia = $request->get('nomeFantasia') ?? '';
        $atividadePrincipal = $request->get('atividadePrincipal') ?? '';
        $atividadeSecundariaI = $request->get('atividadeSecundariaI') ?? '';
        $atividadeSecundariaII = $request->get('atividadeSecundariaII') ?? '';

        $data = array(
            'tipo' => $tipo,
            'doc' => $doc,
            'nome' => $nome,
            'doc_estadual' => $docEstadual,
            'inscricao_municipal' => $inscricaoMunicipal,
            'doc_emissao' => $docEmissao ? implode('-', array_reverse(explode('/', $docEmissao))) : NULL,
            'doc_orgao' => $docOrgao,
            'telefone' => $telefone,
            'email' => $email,
            'cep' => $cep,
            'uf' => $uf,
            'cidade' => $cidade,
            'endereco' => $endereco,
            'numero' => $numero,
            'complemento' => $complemento,
            'bairro' => $bairro,
            'tipoConta' => $tipoConta,
            'banco' => $banco,
            'agencia' => $agencia,
            'conta' => $conta,
            'variacao' => $variacao
        );

        $contribuinte = $this->contribuinte->find($id)->update($data);

        if($inscricaoMunicipal != '')
        {
            $dataCadAlvara = array(
                'atividade_principal' => $atividadePrincipal,
                'atividade_secundaria_I' => $atividadeSecundariaI,
                'atividade_secundaria_II' => $atividadeSecundariaII,
                'inscricao_municipal' => $inscricaoMunicipal
            );
            $cadAlvara = $this->cadAlvara->where('id_contribuinte', $id)->update($dataCadAlvara);

            // caso não possuí cadastro inclui um novo
            if($cadAlvara == false) {
                $dataCadAlvara = array(
                    'id_contribuinte' => $id,
                    'atividade_principal' => $atividadePrincipal,
                    'atividade_secundaria_I' => $atividadeSecundariaI,
                    'atividade_secundaria_II' => $atividadeSecundariaII,
                    'inscricao_municipal' => $inscricaoMunicipal
                );
                $cadAlvara = $this->cadAlvara->create($dataCadAlvara);
            }
        }

        return response()->json([
            "message" => "atualizado com sucesso"
        ], 200);
    }

    public function destroy($id)
    {
        //$contribuinte = $this->contribuinte->find($id)->delete();
        return response()->json(['data' => ['msg' => 'contribuinte removido com sucesso']]);
    }
}
