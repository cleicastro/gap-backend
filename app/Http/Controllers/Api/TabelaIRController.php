<?php

namespace App\Http\Controllers\Api;

use App\Models\TabelaIR;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TabelaIRController extends Controller
{

    private $tabelaIR;
    public function __construct(tabelaIR $tabelaIR)
    {
        $this->tabelaIR = $tabelaIR;
    }

    public function index(Request $request)
    {
        $baseCalculo = $request->get('baseCalculo') ?? '';

        $valores = $this->tabelaIR->all();

        if($baseCalculo != '')
        {
            $newValores = array();
            foreach($valores as $row)
            {
                if($baseCalculo <= $row['ate'])
                {
                    array_push($newValores, $row);
                    break;
                }
            }
            // caso a base de cálculo seja a maior;
            if(count($newValores) == 0)
            {
                array_push($newValores, $valores[count($valores) - 1]);
            }
            $result = ($baseCalculo * ($newValores[0]['aliquota'] / 100));
            $result -= $newValores[0]['deducao'];

            return response()->json([
                'table' => $newValores[0],
                'result' => $result
            ]);
        }

        return response()->json($valores);
    }

    public function show($id)
    {
        $valores = $this->tabelaIR->find($id);
        return response()->json($valores);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        //$valores = $this->tabelaIR->create($data);
        //return response()->json($valores);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        //$valores = $this->tabelaIR->find($id)->update($data);
        //return response()->json($valores);
    }

    public function destroy($id)
    {
        //$valores = $this->tabelaIR->find($id)->delete();
        return response()->json(['data' => ['msg' => 'Imposto removido com sucesso']]);
    }
}
