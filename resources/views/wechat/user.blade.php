<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>个人中心</title>
    <link rel="stylesheet" type="text/css" href="./css/base.css" />
    <link rel="stylesheet" type="text/css" href="./css/user.css" />
</head>
<body>
<!--  -->
<div class="page">
    <section class="top">
        <div class="d-flex d-flex-middle">
            <img src="{{$customer->headimgurl}}" />
            <p>{{$customer->nickname}}</p>
        </div>
    </section>
    <section class="content">
        <section class="userInfo">
            <div class="d-flex d-flex-middle">
                <img src="img/money.png" />
                <p>账户余额（元）</p>
            </div>
            <p class="userMoney">{{$customer->money}}</p>
        </section>
        <section class="meanList">
            <a href="{{route('wechat.order_info')}}" class="d-flex d-flex-middle">
                <img src="img/user_icon_mean.png" />
                <p>我的订菜菜单</p>
            </a>
            <a href="{{route('wechat.log')}}" class="d-flex d-flex-middle">
                <img src="img/user_icon_list.png" />
                <p>我的消费记录</p>
            </a>
        </section>
    </section>
</div>
<!--  -->
<div class="d-flex d-flex-middle d-flex-center" id="tabs">
    <a href="{{route('wechat.index')}}">
        <img src="img/tab1.png" />
        <p>点餐</p>
    </a>
    <a href="{{route('wechat.user')}}" class="active">
        <img src="img/tab2-.png" />
        <p>个人中心</p>
    </a>
</div>
</body>
<script src="js/jquery-3.1.0.min.js"></script>
<script>
    $(function(){
        console.log($('.content').css('top').split('px')[0]*1)
        $('.content').height( $(document).height() - $('.top').height() - $('#tabs').height() + ($('.content').css('top').split('px')[0]*1))
    })
</script>
</html>