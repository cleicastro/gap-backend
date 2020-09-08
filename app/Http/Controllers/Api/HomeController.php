<?php

namespace App\Http\Controllers\Api;

use App\Models\Dam;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    private $dam;
    public function __construct(Dam $dam)
    {
        $this->dam = $dam;
    }

    public function index(Request $request)
    {
        return response()->json([
            'collectToday' => 100,
            'contributors' => 1246,
            'debtors' => 76,
            'ufm' => 2.8,
            'collectThisYear' => array(
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                ),
                array(
                    'issued' => 555,
                    'payd' => 244
                )
            ),
            'collectDebtorsToIncome' => array(
                'incomes' => array(
                    array(
                        'title' => 'IRRF',
                        'value' => 15
                    ),
                    array(
                        'title' => 'ISSQN-PF',
                        'value' => 45
                    ),
                    array(
                        'title' => 'ALVARÁ',
                        'value' => 40
                    ),
                ),
                'data' => array(
                    'datasets' => array(
                        'data' => array(15,45,40)
                    )
                ),
                'label' => array('IRRF','ISSQN-PF','ALVARÁ')
            )


        ]);
    }
}
