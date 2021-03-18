<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('user', 'UserController');
Route::group(['prefix' => 'user'], function () {
    Route::post('signIn', ['as' => 'user.signIn', 'uses' => 'UserController@signIn']);
    Route::post('checkToken', ['as' => 'user.checkToken', 'uses' => 'UserController@checkToken']);
    Route::post('validarEmail', ['as' => 'user.validarEmail', 'uses' => 'UserController@validarEmail']);
    Route::post('cambiarPassword', ['as' => 'user.cambiarPassword', 'uses' => 'UserController@cambiarPassword']);
    Route::post('asignarNivel', ['as' => 'user.asignarNivel', 'uses' => 'UserController@asignarNivel']);
    Route::post('asignarSubNivel', ['as' => 'user.asignarSubNivel', 'uses' => 'UserController@asignarSubNivel']);
});

Route::resource('nivel','NivelController');
Route::group(['prefix'=> 'nivel'], function(){
    Route::post('validarNivel', ['as'=> 'nivel.validarNivel', 'uses' => 'NivelController@validarNivel' ]);
    Route::get('usuario/{user_id}', ['as'=> 'nivel.usuario', 'uses' => 'NivelController@nivelByUsuario' ]);
    
});

Route::resource('sub-nivel','SubNivelController');
Route::group(['prefix'=> 'sub-nivel'], function(){
    Route::post('validarSubNivel', ['as'=> 'sub-nivel.validarSubNivel', 'uses' => 'SubNivelController@validarSubNivel' ]);
    Route::get('{nivel_id}/usuario/{user_id}', ['as'=> 'sub-nivel.usuario', 'uses' => 'SubNivelController@subNivelByUsuario' ]);
});

Route::resource('juego','JuegoController');
Route::group(['prefix' => 'juego'], function () {
    Route::post('upload', ['as' => 'juego.upload', 'uses' => 'JuegoController@uploadOpcionImg']);
    Route::get('imagen/{filename}', ['as' => 'juego.imagen', 'uses' => 'JuegoController@getOpcionImg']);
    Route::get('sub-nivel/{sub_nivel_id}', ['as'=> 'juego.sub-nivel', 'uses' => 'JuegoController@juegoBySubNivel']);
});

Route::resource('glosario','GlosarioController');
Route::group(['prefix' => 'glosario'], function () {
    Route::post('upload', ['as' => 'glosario.upload', 'uses' => 'GlosarioController@uploadOpcionImg']);
    Route::get('imagen/{filename}', ['as' => 'glosario.imagen', 'uses' => 'GlosarioController@getOpcionImg']);
});