<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>我的订菜菜单</title>
    <link rel="stylesheet" type="text/css" href="./css/base.css"/>
    <link rel="stylesheet" type="text/css" href="./css/order.css"/>
</head>
<body class="bg_fff">
<section class="top">
    <div class="topLi">
        <p class="topLi_title">桌号：</p>
        <input value="{{$desk->name}}" readonly="readonly"/>
    </div>
    <div class="topLi">
        <p class="topLi_title">备注：</p>
        <input value="{{$order?$order->remark:''}}" readonly="readonly"/>
    </div>
</section>
<!--  -->
<section class="list">
    <p class="title">已点菜品</p>
    @if(!empty($order))
        @foreach($order->products as $product)
            <div class="li d-flex d-flex-middle d-flex-between">
                {{--<img src="{{\Storage::disk(config('admin.upload.disk'))->url($product->image)}}" class="imgUrl"/>--}}
                <p class="name">{{$product['name']}}</p>
                <p class="num">x{{$product['num']}}</p>
                <p class="price">￥{{$product['total_price']}}</p>
            </div>
        @endforeach
    @endif
</section>
<!--  -->
<a href="{{route('wechat.index')}}" class="addProduct">我要加菜</a>
</body>
</html>