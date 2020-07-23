<div id="order_count" style="height: 400px;width: 100%"></div>
<script src="https://cdn.jsdelivr.net/npm/echarts@4.8.0/dist/echarts.min.js"></script>
<script src="/vendor/echarts/macarons.js"></script>
<script src="/vendor/echarts/china.js"></script>
<script>
    $(function () {
        $.get('/api/order_count').done(function (data) {
            var type = [];      //类型
            var sell = [];      //数据

            $.each(data.products, function (k, v) {
                type.push(v.product.name);
                sell.push({value: v.sum_num, name: v.product.name})
            })

            // console.log(sell);

            var myChart = echarts.init(document.getElementById('order_count'), 'macarons');
            myChart.setOption({
                title: {
                    text: '本月热销商品Top',
                    subtext: data.month_start + ' ~ ' + data.month_end,
                    x: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: type
                },
                series: [
                    {
                        name: '销售量',
                        type: 'pie',
                        radius: '55%',
                        center: ['50%', '50%'],
                        data: sell,
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            });
        });
    });
</script>