<style>
    .panel-body {
        padding: 15px;
    }

    .panel {
        border-radius: 2px;
        box-shadow: none;
        margin-bottom: 20px;
        background-color: #fff;
        border: 1px solid transparent;
    }

    .circle-icon {
        float: left;
        margin-right: 15px;
        width: 50px;
        height: 50px;
        border-radius: 50px;
        color: #fff;
        text-align: center;
        font-size: 20px;
        line-height: 50px;
    }
</style>
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <section class="panel">
            <div class="panel-body">
                <a href="/admin/customers">
                    <div class="circle-icon btn-primary">
                        <i class="glyphicon glyphicon-user"></i>
                    </div>
                </a>
                <div>
                    <h3 class="no-margin" id="s1"></h3> 会员数量
                </div>
            </div>
        </section>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <section class="panel">
            <div class="panel-body">
                <a href="/admin/shop/orders">
                    <div class="circle-icon btn-danger">
                        <i class="glyphicon glyphicon-list"></i>
                    </div>
                </a>
                <div>
                    <h3 class="no-margin" id="s2"></h3> 总订单量
                </div>
            </div>
        </section>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <section class="panel">
            <div class="panel-body">
                    <div class="circle-icon btn-info" >
                        <i class="glyphicon glyphicon-calendar"></i>
                    </div>
                <div>
                    <h3 class="no-margin" id="s3"></h3> 本月营业额
                </div>
            </div>
        </section>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <section class="panel">
            <div class="panel-body">
                <div class="circle-icon btn-success">
                    <i class="glyphicon glyphicon-flag"></i>
                </div>
                <div>
                    <h3 class="no-margin" id="s4"></h3> 总营业额
                </div>
            </div>
        </section>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">

    </div>
</div>
{{--<div id="project_status" style="height: 400px;width: 100%"></div>--}}
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/order_status').done(function (data) {
            console.log(data);
            $("#s1").html(data['customers']);
            $("#s2").html(data['orders']);
            $("#s3").html(data['month']);
            $("#s4").html(data['all']);
        });
    });
</script>