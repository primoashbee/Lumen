<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('/edit/client/{client_id}', 'ClientController@update');
// Route::get('/edit/client/{client_id}', 'ClientController@clientInfo');
Route::get('/structure','API\StructureController@index');
// Route::get('/auth/structure','API\StructureController@auth');
// Route::get('/auth/branches','API\StructureController@branches');    

// Route::post('/revert','RevertController@revert');