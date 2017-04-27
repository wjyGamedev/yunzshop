@extends('layouts.base')

@section('content')
<div>
    <a href="#" class="btn-do-it">支付</a>
</div>
<script type="text/javascript">
    define = null;
    require = null;
</script>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript" charset="utf-8">
        wx.config({"debug":false,"beta":false,"appId":"wx6be17f352e859277","nonceStr":"f9BUapEznF","timestamp":"1493258184","url":"http://test.yunzshop.com/addons/yun_shop/api.php?i=2&mid=null&type=1&route=finance.balance.recharge&i=2&type=1&pay_type=1&recharge_money=50","signature":"cb54d953ee4fefb411497e78db1118c20ea99537","jsApiList":["chooseWXPay"]});
    </script>
    <script>
        $(function(){

            $(".btn-do-it").click(function(){
                wx.chooseWXPay({
                    appId: "wx6be17f352e859277",
                    timestamp: "59014fc851234", // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
                    nonceStr: '1493258184', // 支付签名随机串，不长于 32 位
                    package: 'prepay_id=wx201704270956245959a0f7d20600308143', // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
                    signType: 'MD5', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
                    paySign: 'A31FAAFC7B875D09C31F7520A12D4A1F', // 支付签名
                    success: function (res) {
                        // 支付成功后的回调函数
                        if(res.errMsg == "chooseWXPay:ok" ) {
                            alert('支付成功。');
                        }else{
                            console.log(res);
                            alert("支付失败，请返回重试。");
                        }
                    },
                    fail: function (res) {
                        console.log(res);
                        alert("支付失败，请返回重试。");
                    }
                });
            });
        });
    </script>
@endsection

