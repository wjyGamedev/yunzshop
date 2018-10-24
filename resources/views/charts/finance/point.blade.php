@extends('layouts.base')
@section('title', trans('积分数据统计'))
@section('content')
<link href="{{static_url('yunshop/css/order.css')}}" media="all" rel="stylesheet" type="text/css"/>
<style>
    .point-count{ margin-bottom:20px !important;line-height: 40px;height: 40px;}
    .status-content{ margin: 20px 0;}
    .panel-heading{ border: 0 !important; font-size: 1.2em !important;}
    .panel-body-change{  font-size: 1.5em !important;}
    .daterangepicker .right .calendar-date, .daterangepicker ul, .daterangepicker label, .daterangepicker_end_input{display:none;}
    [name=daterangepicker_start]{width:164px !important;}
</style>
<div class="w1200 m0a">

    <div class="rightlist">
    @include('layouts.tabs')
        <!-- 新增加右侧顶部三级菜单 -->
        <div class="panel panel-info">
            <div class="panel-body">
                <div class="card">
                    <div class="card-content">
                        <form action="" method="post" class="form-horizontal" role="form" id="form1">
                            <div class='form-group col-xs-12 col-sm-6'>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <input type="checkbox" name="search[is_time]" value="1"
                                               @if($search['is_time'] == '1')checked="checked"@endif>
                                    </span>
                                    {!!app\common\helpers\DateRange::tplFormFieldDate('search[time]', [
                                                                            'starttime'=>$search['time']['start'] ?: date('Y-m-d H:i:s'),
                                                                            'endtime'=>$search['time']['end'] ?: date('Y-m-d H:i:s'),
                                                                            'start'=>0,
                                                                            'end'=>0
                                                                            ])!!}
                                </div>
                            </div>
                            <div class="form-group col-xs-12 col-sm-4">
                                <button class="btn btn-success" id="search"><i class="fa fa-search"></i> 搜索</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <table class='table point-count'>
                <tr class='trhead'style="text-align: center;">
                    <td>
                        <h4>可使用积分</h4>
                        <p style="font-size: 1.2em">{{ $pointUseCount }}</p>
                    </td>
                    <td>
                        <h4>已消耗积分</h4>
                        <p style="font-size: 1.2em">{{ $pointUsedCount }}</p>
                    </td>
                    <td>
                        <h4>已赠送积分</h4>
                        <p style="font-size: 1.2em">{{ $pointGivenCount }}</p>
                    </td>
                </tr>
            </table>
        </div>
        <div class="panel panel-default">
            <table class='table' style='float:left;margin-bottom:0;table-layout: fixed;line-height: 40px;height: 40px'>
                <tr class='trhead'>
                    <td colspan='8' style="text-align: left;">
                        积分趋势图
                    </td>
                </tr>
            </table>
        </div>
        <div class='panel panel-default form-horizontal form'>
            <div class='panel-body'style="border: 1px solid #e6e6e6">
                <div id="pointChart" style="width: 100%;height:400px;float: left;"></div>
            </div>
        </div>
        <div class="panel panel-default" style="border: 1px solid #e6e6e6; margin-top: 10px;">
            <div class="panel-heading">详细数据</div>
            <div class='panel-body'>
            <table class="table table-hover table-responsive table-striped">
                <thead>
                    <tr>
                        <th >时间</th>
                        <th >可使用积分</th>
                        <th >已消耗积分</th>
                        <th >已赠送积分</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($AllPointData as $key => $item)
                    <tr>
                        <td>
                            {{ $item['date'] }}
                        </td>
                        <td>
                            {{ $item['usePoint'] }}
                        </td>
                        <td>
                            {{ $item['usedPoint'] }}
                        </td>
                        <td>
                            {{ $item['givenPoint'] }}
                        </td>
                    </tr>
                @endforeach



            </table>
            {!! $page !!}
        </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    //积分统计
    var pointChart = echarts.init(document.getElementById('pointChart'));

    option = {
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data:['已消耗积分','可使用积分','已赠送积分']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: {!! $pointTime !!}
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name:'已消耗积分',
                type:'line',
                stack: '总量',
                data:{!! $pointUsedData !!}
            },
            {
                name:'可使用积分',
                type:'line',
                stack: '总量',
                data:{!! $pointUseData !!}
            },
            {
                name:'已赠送积分',
                type:'line',
                stack: '总量',
                data:{!! $pointGivenData !!}
            },
        ]
    };
    pointChart.setOption(option, true);
</script>
@endsection
