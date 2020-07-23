<div id="sales_amount" style="height: 400px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/sales_amount').done(function (data) {
            console.log(data);
            var myChart = echarts.init(document.getElementById('sales_amount'), 'macarons');
            myChart.setOption({
                title: {
                    text: '本周销售额',
                    subtext: data.week_start + ' ~ ' + data.week_end
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['未付款', '已付款']
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
                    boundaryGap: false,
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
                        name: '未付款',
                        type: 'line',
                        data: data.amount.create,
                        // areaStyle: {normal: {}},
                        // markPoint: {
                        //     data: [
                        //         {type: 'max', name: '最大值'},
                        //         {type: 'min', name: '最小值'}
                        //     ]
                        // },
                        markLine: {
                            data: [
                                {type: 'average', name: '平均值'}
                            ]
                        }
                    },
                    {
                        name: '已付款',
                        type: 'line',
                        data: data.amount.pay,
                        // areaStyle: {normal: {}},
                        // markPoint: {
                        //     data: [
                        //         {type: 'max', name: '最大值'},
                        //         {type: 'min', name: '最小值'}
                        //     ]
                        // },
                        markLine: {
                            data: [
                                {type: 'average', name: '平均值'}
                            ]
                        }
                    }
                ]
            });
        });
    });
</script>