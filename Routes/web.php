<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

if (Module::isEnabled('Integration1C')) {
  Route::prefix('plugins/integration1c')->middleware(['auth'])->group(function() {
    Route::get('/', 'Integration1CController@index');
    Route::post('/setting', 'Integration1CController@setSetting');
  });
}

