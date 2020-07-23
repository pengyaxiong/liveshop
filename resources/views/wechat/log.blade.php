<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>我的消费记录</title>
    <link rel="stylesheet" type="text/css" href="./css/base.css"/>
    <link rel="stylesheet" type="text/css" href="./css/payList.css"/>
</head>
<body>
@foreach($logs as $log)
    <div class="li bg_fff">
        <p class="title">{{$log->description}}</p>
        <p class="time">消费时间：{{$log->created_at}}</p>
        <p class="price">金额：{{$log->type?'+':'-'}}{{$log->money}}元</p>
    </div>
@endforeach
</body>
</html>