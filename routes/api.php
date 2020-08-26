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
    //接收授权手机号算法
    Route::post('/auth_phone', 'IndexController@updateCustomerPhone');
    //系统信息
    Route::get('configs', 'IndexController@configs');
    //首页
    Route::get('index', 'IndexController@index');


    //品牌详情
    Route::get('brands', 'IndexController@brand');
    //商品分类
    Route::get('categories', 'IndexController@categories');
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


    //我的收藏
    Route::get('collect_list', 'IndexController@collect_list');

    //我的订单
    Route::get('order', 'IndexController@order');
    //订单详情
    Route::get('order_info', 'IndexController@order_info');

    //下单
    Route::post('add_order', 'IndexController@add_order');
    //确认订单
    Route::post('checkout', 'IndexController@checkout');
    //取消订单
    Route::post('del_order', 'IndexController@del_order');

    //付款
    Route::post('pay', 'IndexController@pay');
    //付款回调
    Route::any('paid', 'IndexController@paid');
    //退款
    Route::post('refund', 'IndexController@refund');
    //退款回调
    Route::any('refund_back', 'IndexController@refund_back');
    //完成订单
    Route::post('finish_order', 'IndexController@finish_order');



    //课程分类
    Route::get('cms_categories', 'IndexController@cms_categories');
    //课程分类详情
    Route::get('cms_category', 'IndexController@cms_category');
    //课程列表
    Route::get('cms_articles', 'IndexController@cms_articles');
    //课程详情
    Route::get('cms_article/{id}', 'IndexController@cms_article');
    //章节详情
    Route::get('cms_chapter/{id}', 'IndexController@cms_chapter');

    //收藏课程
    Route::post('collect_article', 'IndexController@collect_article');
    //取消收藏
    Route::post('collect_article_del', 'IndexController@collect_article_del');


    //关于我们
    Route::get('about_us', 'IndexController@about_us');
    //意见反馈
    Route::post('feedback', 'IndexController@feedback');
    //加入我们
    Route::post('join_us', 'IndexController@join_us');


    //获取录制模板列表
    Route::post('DescribeLiveRecordTemplates', 'LiveController@DescribeLiveRecordTemplates');
    //创建录制模板
    Route::post('CreateLiveRecordTemplate', 'LiveController@CreateLiveRecordTemplate');
    //修改录制模板
    Route::post('ModifyLiveRecordTemplate', 'LiveController@ModifyLiveRecordTemplate');
    //删除录制模板
    Route::post('DeleteLiveRecordTemplate', 'LiveController@DeleteLiveRecordTemplate');

    //创建录制任务
    Route::post('CreateRecordTask', 'LiveController@CreateRecordTask');
    //终止录制任务
    Route::post('StopRecordTask', 'LiveController@StopRecordTask');
    //删除录制任务
    Route::post('DeleteRecordTask', 'LiveController@DeleteRecordTask');

    //查询直播中的流
    Route::post('DescribeLiveStreamOnlineList', 'LiveController@DescribeLiveStreamOnlineList');
    //创建或者修改推流地址
    Route::post('CreatePush', 'LiveController@CreatePush');
    //断开直播流
    Route::post('DropLiveStream', 'LiveController@DropLiveStream');
    //查询历史流状态
    Route::post('DescribeLiveStreamPublishedList', 'LiveController@DescribeLiveStreamPublishedList');


    //第三方直播接口
    //获取首页一个直播间
    Route::get('getfristlive','LiveController@getFirstLive');
    //获取直播间列表
    Route::get('getlivelist','LiveController@getLiveList');
    //获取录播列表
    Route::get('getreplaylist', 'LiveController@getReplayList');
    //获取直播间信息
    Route::get('getroominfo', 'LiveController@getStreamInfo');
    Route::get('getroomshelves','LiveController@getStreamGoodsShelves');

    /*//原生直播小程序接口
    //创建直播间
    Route::post('createroom','MiniliveController@createLiveRoom');
    //获取直播间列表以及信息
    Route::get('roomlist','MiniliveController@getLiveList');
    //获取直播间回放信息
    Route::get('getroomrelpay','MiniliveController@getLiveReplay');
    //直播间导入商品
    Route::post('pushgoods', 'MiniliveController@pushGoodsToRoom');
    //获取商品列表
    Route::get('getgoods','MiniliveController@getGoodsList');
    //上传图片
    Route::post('uploadimg', 'MiniliveController@uploadImgToWexin');*/


    //导入账号
    Route::post('importuser','MiniliveController@exportChaterRequest');
    //登陆鉴权
    Route::post('userlog','MiniliveController@makeGenSigForRequest');
    //创建聊天室
    Route::post('createchatroom','MiniliveController@createChatRoomByReqest');
    //获取最新一个聊天室
    Route::get('getlatestroom', 'MiniliveController@getFirstRoom');
    //查询聊天室信息
    Route::post('getchatroominfo','MiniliveController@getChatRoomInfo');
    //查询聊天室成员信息
    Route::post('getchatmemberinfo','MiniliveController@getChatMemberInfo');
    //修改聊天室信息
    Route::post('editchatroominfo','MiniliveController@editChatRoomInfo');
    //添加聊天室成员
    Route::post('addchatroommember','MiniliveController@addChatRoomMember');
    //删除聊天室成员
    Route::post('delchatroommember','MiniliveController@delChatRoomMember');
    //修改聊天室成员信息
    Route::post('changechatroommember','MiniliveController@changeChatRoomMemberRole');
    //删除聊天室
    Route::post('delchatroom','MiniliveController@destroyChatRoom');
    Route::post('sendroommsg','LiveController@sendNotification');
    //禁言或者取消禁言
    Route::post('shutupornot','LiveController@shutUp');

    //接收回调
    //接收推断流回调
    Route::post('LiveCallback','TencentCallbackController@LiveCallback');
});