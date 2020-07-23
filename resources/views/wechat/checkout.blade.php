<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>提交</title>
    <link rel="stylesheet" type="text/css" href="./css/base.css" />
    <link rel="stylesheet" type="text/css" href="./css/order.css" />
</head>
<body>
<section class="top bg_fff">
    <div class="topLi">
        <p class="topLi_title">桌号：</p>
        <input value="{{$desk->name}}" readonly="readonly"/>
    </div>
    <div class="topLi">
        <p class="topLi_title">备注：</p>
        <textarea placeholder="备注信息"></textarea>
    </div>
</section>
<!--  -->
<section class="list bg_fff">
    <p class="title">已点菜品</p>
    <div class="li d-flex d-flex-middle d-flex-between">
        <img src="img/logo.png" class="imgUrl"/>
        <p class="name">NIiupi战斧牛排</p>
        <p class="num">x1</p>
        <p class="price">￥69.00</p>
    </div>
    <div class="li d-flex d-flex-middle d-flex-between">
        <img src="img/logo.png" class="imgUrl"/>
        <p class="name">NIiupi战斧牛排</p>
        <p class="num">x1</p>
        <p class="price">￥69.00</p>
    </div>
    <div class="li d-flex d-flex-middle d-flex-between">
        <img src="img/logo.png" class="imgUrl"/>
        <p class="name">NIiupi战斧牛排</p>
        <p class="num">x1</p>
        <p class="price">￥69.00</p>
    </div>
    <p class="total d-flex d-flex-end">共计：¥756.00</p>
</section>
<!--  -->
<p class="bottomView"></p>
<a href="order.html" class="submit">确认订单</a>
</body>
</html>