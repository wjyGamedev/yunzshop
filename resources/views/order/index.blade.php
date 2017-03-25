﻿@extends('layouts.base')

@section('content')
    <div class="w1200 m0a">
        <link href="../addons/sz_yi/static/js/dist/select2/select2.css" rel="stylesheet">
        <link href="../addons/sz_yi/static/js/dist/select2/select2-bootstrap.css" rel="stylesheet">
        <script language="javascript" src="../addons/sz_yi/static/js/dist/select2/select2.min.js"></script>
        <script language="javascript" src="../addons/sz_yi/static/js/dist/select2/select2_locale_zh-CN.js"></script>
        <script type="text/javascript" src="./resource/js/lib/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="../addons/sz_yi/static/js/dist/jquery.gcjs.js"></script>
        <script type="text/javascript" src="../addons/sz_yi/static/js/dist/jquery.form.js"></script>
        <script type="text/javascript" src="../addons/sz_yi/static/js/dist/tooltipbox.js"></script>
        <style type='text/css'>
            .trhead td {
                background: #f8f8f8;
                text-align: center
            }

            .trbody td {
                text-align: center;
                vertical-align: top;
                border-left: 1px solid #DEDEDE;
                overflow: hidden;
            }

            .goods_info {
                position: relative;
                width: 60px;
            }

            .goods_info img {
                width: 50px;
                background: #fff;
                border: 1px solid #DEDEDE;
                padding: 1px;
            }

            .goods_info:hover {
                z-index: 1;
                position: absolute;
                width: auto;
            }

            .goods_info:hover img {
                width: 320px;
                height: 320px;
            }

            .form-control .select2-choice {
                border: 0 none;
                border-radius: 2px;
                height: 32px;
                line-height: 32px;
            }
        </style>
        <div class="rightlist">
            <div class="panel panel-default" style='margin-top:60px'>
                <div class="panel-body sx-border">
                    <form action="./index.php" method="get" class="form-horizontal" role="form" id="form1">
                        <input type="hidden" name="c" value="site"/>
                        <input type="hidden" name="a" value="entry"/>
                        <input type="hidden" name="m" value="sz_yi"/>
                        <input type="hidden" name="do" value="order" id="form_do"/>
                        <input type="hidden" name="route" value="{{$url}}" id="form_p"/>
                        <div class="form-group">
                            <div class="col-sm-8 col-lg-12 col-xs-12">
                                @section('search_bar')
                                <div class='input-group'>
                                    <select name="search[ambiguous][field]" class="form-control">
                                        <option value="order"
                                                @if(array_get($requestSearch,'ambiguous.field','') =='order')  selected="selected"@endif >
                                            订单号/支付号
                                        </option>
                                        <option value="member"
                                                @if( array_get($requestSearch,'ambiguous.field','')=='member')  selected="selected"@endif>
                                            用户姓名/ID/昵称/手机号
                                        </option>
                                        <option value="order_goods"
                                                @if( array_get($requestSearch,'ambiguous.field','')=='order_goods')  selected="selected"@endif>
                                            商品名称/ID
                                        </option>
                                        <option value="dispatch"
                                                @if( array_get($requestSearch,'ambiguous.field','')=='dispatch')  selected="selected"@endif>
                                            快递单号
                                        </option>
                                    </select>
                                    <input class="form-control" name="search[ambiguous][string]" type="text"
                                           value="{{array_get($requestSearch,'ambiguous.string','')}}"
                                           placeholder="订单号/支付单号">
                                </div>
                                <div class='input-group'>

                                    <select name="search[pay_type]" class="form-control">
                                        <option value=""
                                                @if( array_get($requestSearch,'pay_type',''))  selected="selected"@endif>
                                            支付方式
                                        </option>
                                        <option value="1"
                                                @if( array_get($requestSearch,'pay_type','') == '1')  selected="selected"@endif>
                                            在线支付
                                        </option>
                                        <option value="2"
                                                @if( array_get($requestSearch,'pay_type','') == '2')  selected="selected"@endif>
                                            货到付款
                                        </option>
                                        <option value="3"
                                                @if( array_get($requestSearch,'pay_type','') == '3')  selected="selected"@endif>
                                            余额支付
                                        </option>
                                    </select>
                                </div>
                                <div class='input-group'>

                                    <select name="search[time_range][field]" class="form-control">
                                        <option value=""
                                                @if( array_get($requestSearch,'time_range.field',''))selected="selected"@endif >
                                            操作时间
                                        </option>
                                        <option value="create_time"
                                                @if( array_get($requestSearch,'time_range.field','')=='create_time')  selected="selected"@endif >
                                            下单
                                        </option>
                                        <option value="pay_time"
                                                @if( array_get($requestSearch,'time_range.field','')=='pay_time')  selected="selected"@endif>
                                            付款
                                        </option>
                                        <option value="sent_time"
                                                @if( array_get($requestSearch,'time_range.field','')=='sent_time')  selected="selected"@endif>
                                            发货
                                        </option>
                                        <option value="finish_time"
                                                @if( array_get($requestSearch,'time_range.field','')=='finish_time')  selected="selected"@endif>
                                            完成
                                        </option>
                                    </select>
                                    {!! tpl_form_field_daterange(
                                        'search[time_range]',
                                        array(
                                            'starttime'=>array_get($requestSearch,'time_range.start',0),
                                            'endtime'=>array_get($requestSearch,'time_range.end',0),
                                            'start'=>0,
                                            'end'=>0
                                        ),
                                        true
                                        )!!}

                                </div>
                                @show
                            </div>
                        </div>

                        <div class="form-group">

                            <div class="col-sm-7 col-lg-9 col-xs-12">
                                <button class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
                                <input type="hidden" name="token" value="{{$var['token']}}"/>
                                <button type="button" name="export" value="1" id="export" class="btn btn-primary">导出
                                    Excel
                                </button>
                                @if( $requestSearch['plugin'] != "fund")
                                    <a class="btn btn-warning"
                                       href="{php echo $this->createWebUrl('order/export')}">自定义导出</a>
                                @endif
                            </div>

                        </div>

                    </form>
                </div>
            </div>

            <table class='table'
                   style='float:left;margin-bottom:0;table-layout: fixed;line-height: 40px;height: 40px'>
                <tr class='trhead'>
                    <td colspan='8' style="text-align: left;">
                        订单数: <span id="total">{{$list['total']}}</span>
                        订单金额: <span id="totalmoney" style="color:red">{{$total_price}}</span>元&nbsp;
                        @if(0)
                            结算金额: <span style="color:red">
                                @if( $costmoney>0){{$costmoney}}</span>元
                            &nbsp;<a class="btn btn-default"
                                     href="{php echo $this->createWebUrl('order/list',array('applytype'=>1))}">提现</a>
                            @if( !empty($shopset['weixin']))
                                <a class='btn btn-default' onclick="return confirm('确认微信钱包提现?')"
                                   href="{php echo $this->createWebUrl('order/list',array('applytype'=>2));}">微信提现</a>
                            @endif
                        @else
                            没有可提现金额
                        @endif
                        @endif
                    </td>
                </tr>
            </table>

            @foreach ($list['data'] as $order)
                <table class='table'
                       style='float:left;border:1px solid #ccc;margin-top:5px;margin-bottom:0px;table-layout: fixed;'>
                    <tr>
                        <td colspan='8' style='border-bottom:1px solid #ccc;background:#f8f8f8;'>
                            <b>订单编号:</b> {{$order['order_sn']}}
                            @if( 0&&$order['pay_ordersn']=0)
                                <b>支付单号:</b>  {{$order['pay_ordersn']=0}}
                            @endif
                            <b>下单时间: </b>{!! date('Y-m-d H:i:s', $order['create_time']) !!}
                            @if( 0&&!empty($order['refundstate']))<label
                                    class='label label-danger'>{{$r_type[$order['rtype']]}}申请</label>@endif
                            @if( 0&&$order['rstatus'] == 4)<label class='label label-primary'>客户已经寄出快递</label>@endif
                            @section('shop_name')
                            <label class="label label-info">总店</label>
                            @show
                            @if( 0&&!empty($order['storename']))
                                <label class="label label-primary">所属门店：{{$order['storename']}}</label>
                        @endif
                        <td style='border-bottom:1px solid #ccc;background:#f8f8f8;text-align: center'>
                            @if( 0&&empty($order['statusvalue']))
                                <a class="btn btn-default btn-sm" href="javascript:;"
                                   onclick="$('#modal-close').find(':input[name=id]').val('{{$order['id']}}')"
                                   data-toggle="modal" data-target="#modal-close">关闭订单</a>
                            @endif

                        </td>

                        @if( 0&&empty($var['isagent']) && $order['isempty'] == 1 && $order['ismaster'] == 1)
                            <td style="...">
                                <input class='itemid' type='hidden' value="{{$order['id']}}"/>
                                <a class="btn btn-primary btn-sm" href="javascript:;" onclick="sendagent(this)"
                                   data-toggle="modal" data-target="#modal-changeagent">选择门店</a>
                            </td>
                        @endif


                    </tr>
                </table>
                <table class='table' style='float:left;border:1px solid #ccc;border-top:none;table-layout: fixed;'>

                    @foreach( $order['has_many_order_goods'] as $order_goods_index => $order_goods)
                        <tr class='trbody'>
                            <td class="goods_info">
                                <img src="@if( 0&&$order['cashier']==1){{$order['name']['thumb']}}@else{!! tomedia($order_goods['thumb']) !!}@endif">
                            </td>
                            <td valign='top' style='border-left:none;text-align: left;/*width:400px*/;'>
                                @if( 0&&$order['cashier']==1){{$order['name']['name']}}@else{{$order_goods['title']}}@endif @if( !empty($order_goods['optiontitle']))
                                    <br/><span
                                            class="label label-primary sizebg">{{$order_goods['optiontitle']}}</span>@endif

                                <br/>{{$order_goods['goods_sn']}}
                            </td>
                            <td style='border-left:none;text-align:left;/*width:150px*/'>@if( $requestSearch['plugin'] != "fund")
                                    原价: {!! number_format(
                                    $order_goods['goods_price']/$order_goods['total'],2)!!} @endif<br/>应付: {!! number_format($order_goods['price']/$order_goods['total'],2) !!}
                                <br/>数量: {{$order_goods['total']}}
                            </td>

                            @if( $order_goods_index==0)
                                <td rowspan="{!! count($order['has_many_order_goods']) !!}">
                                    <a href="{!! yzAppUrl('member/list',array('op'=>'detail')) !!}"> {{$order['belongs_to_member']['nickname']}}</a>
                            @else
                                {{$order['belongs_to_member']['nickname']}}
                            @endif
                                <br/>
                                {{$order['belongs_to_member']['realname']}}
                                <br/>{{$order['belongs_to_member']['mobile']}}
                            </td>
                                <td rowspan="{!! count($order['has_many_order_goods']) !!}">
                                    @if( $order['status'] > 0)
                                        <label class='label label-1}'>{{$order['has_one_pay_type']['name']}}</label>
                                        <br/>
                                    @elseif (0&&$order['statusvalue'] == 0)
                                        @if( 0&&$order['paytypevalue'] == 3)
                                            <label class='label label-default'>货到付款</label><br/>
                                        @else
                                            <label class='label label-default'>未支付</label><br/>
                                        @endif
                                    @elseif( 0&&$order['statusvalue'] == -1)
                                        <label class='label label-default'>{{$order['paytype']}}</label><br/>
                                    @endif
                                    {{$order['has_one_dispatch_type']['name']}}
                                    @if( 0&&$order['addressid']!=0 && $order['statusvalue']>=2)<br/>
                                    <button type='button' class='btn btn-default btn-sm'
                                            onclick='express_find(this,"{{$order['id']}}")'>查看物流
                                    </button>
                                    @endif
                                </td>
                                <td rowspan="{php echo count($order['has_many_order_goods'])}" style='width:18%;'>
                                    <table style='width:100%;'>
                                        <tr>
                                            <td style='border:none;text-align:right;'>商品小计：</td>
                                            <td style='border:none;text-align:right;;'>￥{!! number_format(
                                                $order['goods_price'] ,2) !!}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='border:none;text-align:right;'>运费：</td>
                                            <td style='border:none;text-align:right;;'>￥{!! number_format(
                                                $order['dispatch_price'],2) !!}
                                            </td>
                                        </tr>


                                        @if( empty($order['statusvalue']))
                                            <tr>
                                                <td style='border:none;text-align:right;'></td>
                                                @if(0)
                                                    @if( 0&&$order['ischangePrice'] == 1)
                                                        <td style='border:none;text-align:right;color:green;'>
                                                            <a href="javascript:;" class="btn btn-link "
                                                               onclick="changePrice('{{$order['id']}}')">修改价格</a>
                                                        </td>
                                                    @endif
                                                @endif
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                                <td rowspan="{php echo count($order['has_many_order_goods'])}"><label
                                            class='label label-{{$order['statuscss']=0}}'>{{$order['status_name']}}</label><br/>
                                    <a href="{!! yzWebUrl('order.detail',['id'=>$order['id']])!!}">查看详情</a>
                                </td>
                                <td rowspan="{php echo count($order['has_many_order_goods'])}" width="10%">
                                    @section('operation')
                                        @include('order.ops')
                                    @show
                                </td>
                        </tr>
                    @endforeach
                    <tr>

                    </tr>
                </table>
            @endforeach


            <div id="pager">{!! $pager !!}</div>
        </div>
    </div>
    <script language="javascript">


        function send(btn) {
            var modal = $('#modal-confirmsend');
            var itemid = $(btn).parent().find('.itemid').val();
            modal.find(':input[name=id]').val(itemid);
            var addressdata = eval('(' + $(btn).parent().find('.addressdata').val() + ')');
            modal.find('.realname').html(addressdata.realname);
            modal.find('.mobile').html(addressdata.mobile);
            modal.find('.address').html(addressdata.address);
        }
        $(function () {
            $.ajax({
                url: window.location.href,
                type: 'post',
                success: function (serverData) {
                    if (serverData) {
                        eval("var data=" + serverData + "");
                        $("#pager").html(data.result.pager);
                        $("#total").html(data.result.total);
                        $("#totalmoney").html(data.result.totalmoney);
                    }
                }
            })
            $('.select2').select2({
                search: true,
                placeholder: "请选择门店",
                allowClear: true
            });
        });
        function sendagent(btn) {
            var modal = $('#modal-changeagent');
            var itemid = $(btn).parent().find('.itemid').val();
            modal.find(':input[name=id]').val(itemid);
        }
    </script>
@endsection('content')