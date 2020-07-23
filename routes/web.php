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
    return redirect('/admin');
});
//微信商城前端'middleware' => ['wechat.oauth','wechat'],
Route::group(['namespace' => 'Wechat',  'prefix' => 'wechat','as' => 'wechat.'], function () {


    //菜品列表
    Route::get('/index', 'IndexController@index')->name('index');
    //购物车加减
    Route::post('/cart_cut', 'IndexController@cart_cut')->name('cart_cut');
    Route::post('/cart_add', 'IndexController@cart_add')->name('cart_add');

    //确认订单页&确认加菜页
    Route::get('/order', 'IndexController@order')->name('order');

    //点菜&加菜
    Route::post('/add', 'IndexController@add')->name('add');
    Route::post('/delete', 'IndexController@delete')->name('delete');

    //下单
    Route::post('/do_order', 'IndexController@do_order')->name('do_order');
    Route::post('/do_add', 'IndexController@do_add')->name('do_add');

    //结算
    Route::get('/checkout', 'IndexController@checkout')->name('checkout');
    //我的订单页
    Route::get('/order_info', 'IndexController@order_info')->name('order_info');

    Route::get('/user', 'IndexController@user')->name('user');
    Route::get('/log', 'IndexController@log')->name('log');

});


