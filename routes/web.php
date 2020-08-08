<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'StaffController@index');

Route::post('/staff/pay', 'StaffController@pay');
Route::resource('/staff', 'StaffController')->except('update');
Route::post('/staff/{staff}', 'StaffController@update');
