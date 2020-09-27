<?php

namespace App\Http\Controllers\Api;

use App\Models\Receita;
use App\Http\Resources\receitaResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReceitaController extends Controller
{

    private $receita;
    public function __construct(receita $receita)
    {
        $this->receita = $receita;
    }

    public function index()
    {
        $receitas = $this->receita->orderBy('sigla', 'ASC')->get();
        return response()->json($receitas);
        //return new receitaResource($receitas);
    }

    public function show($id)
    {
        $receitas = $this->receita->find($id);
        //return response()->json($receitas);
        return new receitaResource($receitas);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        //$receita = $this->receita->create($data);
        //return response()->json($receita);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        //$receita = $this->receita->find($id)->update($data);
        //return response()->json($receita);
    }

    public function destroy($id)
    {
        //$receita = $this->receita->find($id)->delete();
        return response()->json(['data' => ['msg' => 'receita removido com sucesso']]);
    }
}
