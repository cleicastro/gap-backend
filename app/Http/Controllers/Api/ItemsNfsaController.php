<?php

namespace App\Http\Controllers\Api;

use App\Models\ItemNfsa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ItemsNfsaController extends Controller
{
    private $item;
    public function __construct(ItemNfsa $item)
    {
        $this->item = $item;
    }

    public function index() {
        $items = $this->item->paginate(30);
        return response()->json($items);
    }

    public function show($id) {
        return response()->json([
            "message" => "page not found"
        ], 404);
    }

    public function store(Request $request) {
        date_default_timezone_set('America/Sao_Paulo');
        $data = $request->all();
        $items = $this->item->create($data);
        return response()->json($items);
    }

    public function destroy($id) {
        try {
            $item = $this->item->find($id)->delete();
            return response()->json(['message' => 'Item removido com sucesso']);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => "falha ao remover este item",
                "erro" => $th
            ], 501);
        }
    }
}
