<div id="sales_count" style="height: 400px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/sales_count').done(function (data) {
            console.log(data);
            var myChart = echarts.init(document.getElementById('sales_count'), 'macarons');
            myChart.setOption({
                title: {
                    text: '本周订单数',
                    subtext:  data.week_start + ' ~ ' + data.week_end
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data: ['下单', '付款', '出库', '交易成功']
                },
                toolbox: {
                    show: true,
                    feature: {
                        dataZoom: {},
                        dataView: {readOnly: false},
                        magicType: {type: ['line', 'bar']},
                        restore: {},
                        saveAsImage: {}
                    }
                },

                xAxis: {
                    type: 'category',
                    data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value}'
                    }
                },
                series: [
                    {
                        name: '下单',
                        type: 'bar',
                        data: data.count.create,
                    },
                    {
                        name: '付款',
                        type: 'bar',
                        data: data.count.pay,
                    },
                    {
                        name: '出库',
                        type: 'bar',
                        data: data.count.shipping,
                    },
                    {
                        name: '交易成功',
                        type: 'bar',
                        data: data.count.finish,
                    }
                ]
            });
        });
    });
</script>