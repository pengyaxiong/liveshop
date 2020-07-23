<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');

    //商城管理
    Route::group(['prefix' => 'shop', 'namespace' => 'Shop', 'as' => 'shop.'], function (Router $router) {

        //品牌管理
        $router->resource('brands', 'BrandController');
        //品类管理
        $router->resource('categories', 'CategoryController');
        //设计师管理
        $router->resource('designers', 'DesignerController');
        //商品管理
        $router->resource('products', 'ProductController');
        //优惠券管理
        $router->resource('coupons', 'CouponController');
        //订单管理
        $router->resource('orders', 'OrderController');
        //流水管理
        $router->resource('bills', 'BillController');


    });

    $router->resource('configs', 'ConfigController');

    $router->resource('customers', 'CustomerController');

});
