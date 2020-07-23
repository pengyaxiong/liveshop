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

Route::group(['namespace' => 'Api'], function () {

    //订单状态统计
    Route::get('order_status', 'VisualizationController@order_status');
    //本月热门销量
    Route::get('order_count', 'VisualizationController@order_count');
    //本周销售额
    Route::get('sales_amount', 'VisualizationController@sales_amount');
    //本周订单数
    Route::get('sales_count', 'VisualizationController@sales_count');
    //会员注册量
    Route::get('statistics_customer', 'VisualizationController@statistics_customer');

});

Route::group(['namespace' => 'Api', 'prefix' => 'wechat', 'as' => 'wechat.'], function () {

    //授权
    Route::post('/auth', 'IndexController@auth');
    //系统信息
    Route::get('configs', 'IndexController@configs');
    //首页
    Route::get('index', 'IndexController@index');


    //品牌
    Route::get('brands', 'IndexController@brands');
    //品牌详情
    Route::get('brand/{id}', 'IndexController@brand');
    //商品分类
    Route::get('categories', 'IndexController@categories');
    //分类商品
    Route::get('category/{id}', 'IndexController@category');
    //设计师
    Route::get('designers', 'IndexController@designers');
    //设计师详情
    Route::get('designer/{id}', 'IndexController@designer');
    //商品
    Route::get('products', 'IndexController@products');
    //商品详情
    Route::get('product/{id}', 'IndexController@product');
    //搜索
    Route::get('search', 'IndexController@search');

    //添加到购物车
    Route::post('add_cart', 'IndexController@add_cart');
    //购物车列表
    Route::get('cart', 'IndexController@cart');
    //删除选中
    Route::post('destroy_checked', 'IndexController@destroy_checked');
    //修改购物车商品数量
    Route::post('change_num', 'IndexController@change_num');
    //收藏商品
    Route::post('collect_product', 'IndexController@collect_product');
    //取消收藏
    Route::post('collect_product_del', 'IndexController@collect_product_del');

    //用户信息
    Route::get('customer', 'IndexController@customer');
    //我的地址
    Route::get('address', 'IndexController@address');
    //新增地址
    Route::post('add_address', 'IndexController@add_address');
    //修改地址
    Route::get('edit_address', 'IndexController@edit_address');
    //更新地址
    Route::post('update_address', 'IndexController@update_address');
    //删除地址
    Route::post('delete_address', 'IndexController@delete_address');



    //我的订单
    Route::get('order', 'IndexController@order');
    //订单详情
    Route::get('order_info', 'IndexController@order_info');

    //下单
    Route::post('add_order', 'IndexController@add_order');
    //确认订单
    Route::post('checkout', 'IndexController@checkout');

    //付款
    Route::post('pay', 'IndexController@pay');
    //付款回调
    Route::any('paid', 'IndexController@paid');
    //完成订单
    Route::post('finish_order', 'IndexController@finish_order');




});