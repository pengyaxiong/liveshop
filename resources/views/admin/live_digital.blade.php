<div id="live_digital" style="height: 400px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    var id = "{{$room_id}}";
    $(function () {
        $.get('/api/live_room_aount?id='+id).done(function (data) {
            console.log(data);
            var myChart = echarts.init(document.getElementById('live_digital'), 'macarons');
            myChart.setOption({
                title: {
                    text: '本周直播间详情',
                    subtext: data.week_start + ' ~ ' + data.week_end
                },
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['直播间观看人数', '浏览商品人数','购买商品人数']
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
                        name: '在线人数',
                        type: 'line',
                        data: data.amount.live_rooms_view,
                        markLine: {
                            data: [
                                {type: 'average', name: '平均值'}
                            ]
                        }
                    },
                    {
                        name: '浏览商品人数',
                        type: 'line',
                        data: data.amount.live_rooms_product_view,
                        markLine: {
                            data: [
                                {type: 'average', name: '平均值'}
                            ]
                        },
                        name: '购买人数',
                        type: 'line',
                        data: data.amount.buy,
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