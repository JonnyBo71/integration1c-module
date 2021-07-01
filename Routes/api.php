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

Route::middleware('auth:api')->get('/integration1c', function (Request $request) {
    return $request->user();
});

Route::prefix('plugins/integration1c')->group(function() {
  Route::get('/import', 'Integration1CController@import');
  Route::get('/export', 'Integration1CController@export');

  Route::get('/guids', 'Integration1CController@guids');
  Route::get('/products', 'Integration1CController@products');

  Route::get('/setting', 'Integration1CController@getSetting');
  Route::post('/setting', 'Integration1CController@setSetting');
});
