<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function() {
    Route::post('/register', 'AuthenticationController@register');
    Route::post('/login', 'AuthenticationController@login');
    Route::post('/logout', 'AuthenticationController@logout');
    // Route::middleware('auth:api')->group(function() {
    //     Route::post('/logout', 'AuthenticationController@logout');
    // });
});

Route::namespace('Api')->group(function() {
    Route::prefix('dam')->group(function() {
        Route::get('/', 'DamController@index');
        Route::post('/', 'DamController@store');
        Route::get('/{id}', 'DamController@show');
        Route::put('/{id}', 'DamController@update');
        Route::delete('/{id}', 'DamController@destroy');
    });

    Route::prefix('nfsa')->group(function() {
        Route::get('/', 'NfsaController@index');
        Route::post('/', 'NfsaController@store');
        Route::get('/{id}', 'NfsaController@show');
        Route::put('/{id}', 'NfsaController@update');
        Route::delete('/{id}', 'NfsaController@destroy');
    });

    Route::prefix('item')->group(function() {
        Route::get('/', 'ItemsNfsaController@index');
        Route::post('/', 'ItemsNfsaController@store');
        Route::get('/{id}', 'ItemsNfsaController@show');
        Route::delete('/{id}', 'ItemsNfsaController@destroy');
    });

    Route::prefix('alvara-funcionamento')->group(function() {
        Route::get('/', 'AlvaraFuncionamentoController@index');
        Route::post('/', 'AlvaraFuncionamentoController@store');
        Route::get('/{id}', 'AlvaraFuncionamentoController@show');
        Route::put('/{id}', 'AlvaraFuncionamentoController@update');
        Route::delete('/{id}', 'AlvaraFuncionamentoController@destroy');
    });

    Route::apiResource('contribuinte','ContribuinteController');
    Route::apiResource('receita','ReceitaController');
    Route::apiResource('tabela-ir', 'TabelaIRController');
    Route::apiResource('home', 'HomeController');
});
