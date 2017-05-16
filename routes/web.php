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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

// 管理側
Route::group(['prefix' => 'admin'], function() {
	// ログイン関連
	Route::get('login', 'Admin\Auth\LoginController@showLoginForm');
	Route::post('login', 'Admin\Auth\LoginController@login');
	Route::post('logout', 'Admin\Auth\LoginController@logout');

	// 管理者登録
    Route::get('register', 'Admin\Auth\RegisterController@index');
    Route::post('register', 'Admin\Auth\RegisterController@register');

	Route::get('/', 'Admin\HomeController@index');
	Route::get('/home', 'Admin\HomeController@index');
});