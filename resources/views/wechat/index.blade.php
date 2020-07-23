<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title></title>
    <link rel="stylesheet" type="text/css" href="./css/base.css"/>
    <link rel="stylesheet" type="text/css" href="./css/index.css"/>
</head>
<body>
<!-- top -->
<section class="top">
    <img src="{{\Storage::disk(config('admin.upload.disk'))->url($config->image)}}"/>
</section>
<!-- list -->
<section class="list d-flex d-flex-between">
    <section class="classify">
        <div class="d-flex d-flex-middle d-flex-center active">
            <img src="img/hot.png"/>
            <p>热卖</p>
        </div>
        @foreach($categories as $category)
            <div class="d-flex d-flex-middle d-flex-center">
                <p>{{$category->name}}</p>
            </div>
        @endforeach
    </section>
    <section class="productList">
        @if(!empty($hot))
            @foreach($hot as $food)
                <section class="productItem d-flex d-flex-between">
                    <img class="productUrl" src="{{\Storage::disk(config('admin.upload.disk'))->url($food->image)}}">
                    <div class="productInfo"><p class="productName">{{$food->name}}</p>
                        <p class="productGift">{{$food->description}}</p>
                        <p class="productNum">月售:{{$food->sale_num}}</p>
                        <p class="productPrice">￥{{$food->price}}/{{$food->unit}}</p></div>
                    <div class="btns d-flex d-flex-middle d-flex-end"><img src="img/cut.png" data-id="{{$food->id}}" class="cut hide">
                        <p class="num hide">0</p><img src="img/add.png" class="add" data-id="{{$food->id}}" ></div>
                </section>
            @endforeach
        @endif
    </section>
    @foreach($categories as $category)
        <section class="productList hide">
            @if(!empty($category->foods))
                @foreach($category->foods as $food)
                    <section class="productItem d-flex d-flex-between">
                        <img class="productUrl" src="{{\Storage::disk(config('admin.upload.disk'))->url($food->image)}}">
                        <div class="productInfo"><p class="productName">{{$food->name}}</p>
                            <p class="productGift">{{$food->description}}</p>
                            <p class="productNum">月售:{{$food->sale_num}}</p>
                            <p class="productPrice">￥{{$food->price}}/{{$food->unit}}</p></div>
                        <div class="btns d-flex d-flex-middle d-flex-end"><img src="img/cut.png" data-id="{{$food->id}}" class="cut hide">
                            <p class="num hide">0</p><img src="img/add.png" data-id="{{$food->id}}" class="add"></div>
                    </section>
                @endforeach
            @endif
        </section>
    @endforeach


<!--  -->
    <section class="shopCar d-flex d-flex-middle">
        <section class="shopCarView d-flex">
            <div class="shopcarNum">
                <p class="scNum">{{count($cart)}}</p>
            </div>
            <p class="shopCarPrice">￥{{$total_price}}</p>
        </section>
        <p class="submit">去结算</p>
    </section>
    <!--  -->
</section>
<!-- 底部菜单 -->
<div class="d-flex d-flex-middle d-flex-center" id="tabs">
    <a href="{{route('wechat.index')}}" class="active">
        <img src="img/tab1-.png"/>
        <p>点餐</p>
    </a>
    <a href="{{route('wechat.user')}}">
        <img src="img/tab2.png"/>
        <p>个人中心</p>
    </a>
</div>
<!-- 购物车弹窗 -->
<section class="alert">
    <p class="bg"></p>
    <section class="alertShopCar">
        <p class="asc_title">已点餐</p>
        <section class="alertShopCarList">

        </section>
    </section>
</section>
</body>
<script src="js/jquery-3.1.0.min.js"></script>
<script>
    $(function () {
        $('.list').height($(document).height() - $('.top img').height() - $('#tabs').height())
        //
        $('.classify div').on('click', function () {
            $(this).addClass('active').siblings().removeClass('active')
            $('.productList').eq($(this).index()).removeClass('hide').siblings('.productList').addClass('hide')
        })

        //
        $('.add').on('click',function () {
            var num = ($(this).siblings('.num').text()) * 1
            if (num == 0) {
                $(this).siblings('.num').removeClass('hide')
                $(this).siblings('.cut').removeClass('hide')
            }
            $(this).siblings('.num').text(num + 1);
            var food_id=$(this).data('id');
            $.ajax({
                type: "post",
                url: "/wechat/cart_add",
                data: {
                    food_id:food_id,
                    type:0,
                },
                success: function (data) {
                    if (data.status == 0) {
                        alert(data.msg);
                        return false;
                    }
                    location.href=location.href;
                }
            });
        })
        $('.cut').on('click',function () {
            var num = ($(this).siblings('.num').text()) * 1
            if (num == 1) {
                $(this).siblings('.num').addClass('hide')
                $(this).addClass('hide')
            }
            $(this).siblings('.num').text(num - 1)
        })
        //
        $('.shopcarNum').click(function () {
            $('.alert').toggle()
            $('.shopCar').toggleClass('fixedBottom')
        })
        $('.alert .bg').click(function () {
            $('.alert').toggle()
            $('.shopCar').toggleClass('fixedBottom')
        })
        //
        $('.submit').click(function () {
            window.location.href = '{{route('wechat.checkout')}}'
        })
    })
</script>
</html>