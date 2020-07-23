<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
class HomeController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('统计图表')
            ->description('Description...')
//            ->row(Dashboard::title())
            ->row(function (Row $row) {

                $row->column(12, function (Column $column) {
                    $column->append(new Box('', view('admin.order_status')));
                });

                $row->column(12, function (Column $column) {
                    $column->append(new Box('本月热门销量', view('admin.order_count')));
                });

                $row->column(12, function (Column $column) {
                    $column->append(new Box('本周销售额', view('admin.sales_amount')));
                });

                $row->column(12, function (Column $column) {
                    $column->append(new Box('本周订单数', view('admin.sales_count')));
                });

                $row->column(12, function (Column $column) {
                    $column->append(new Box('会员注册量', view('admin.statistics_customer')));
                });
            });
    }
}
