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
        //商品管理
        $router->resource('products', 'ProductController');
        //优惠券管理
        $router->resource('coupons', 'CouponController');
        //订单管理
        $router->resource('orders', 'OrderController');
        //流水管理
        $router->resource('bills', 'BillController');

    });
    //课程管理
    Route::group(['prefix' => 'cms', 'namespace' => 'Cms', 'as' => 'cms.'], function (Router $router) {

        //分类管理
        $router->resource('categories', 'CategoryController');
        //课程管理
        $router->resource('articles', 'ArticleController');
        //章节管理
        $router->resource('chapters', 'ChapterController');
    });
    
    //直播管理
    Route::group(['prefix' => 'live', 'namespace' => 'Live', 'as' => 'live.'], function (Router $router){
        //直播间管理
        $router->resource('rooms', 'LiveController');
        //获取商品接口
        $router->get('getproducts', 'ApiController@getProducts');
        //获取优惠券接口
        $router->get('getcoupons', 'ApiController@getCoupons');
        //直播间商品和优惠券
        $router->get('editgoods','OtherController@editGoods');
        //直播间数据统计
        $router->get('digital','OtherController@digital');
        //接收设置直播间商品和优惠券数据
        $router->post('setgoods','OtherController@setGoods');
    });
    
    //关于我们
    $router->resource('abouts', 'AboutController');
    //意见反馈
    $router->resource('feedback', 'FeedbackController');
    //加入我们
    $router->resource('join-uses', 'JoinUsController');

    $router->resource('configs', 'ConfigController');

    $router->resource('customers', 'CustomerController');

});
