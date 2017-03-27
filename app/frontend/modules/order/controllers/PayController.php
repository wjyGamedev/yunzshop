<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2017/3/9
 * Time: 上午9:38
 */

namespace app\frontend\modules\order\controllers;

use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\models\Order;
use app\common\services\PayFactory;
use app\frontend\modules\order\services\VerifyPayService;
use Ixudra\Curl\Facades\Curl;

class PayController extends BaseController
{
    public function index()
    {
        //返回支付方式列表
    }

    public function wechatPay()
    {
        $pay = PayFactory::create(PayFactory::PAY_WEACHAT);
        $Order = Order::first();
        /*$result = $pay->setyue('50');
        if($result == false){
            $this->errorJson($pay->getMessage());
        }*/
        $query_str = [
            'order_no' => time(),
            'amount' => $Order->price,
            'subject' => '微信支付',
            'body' => '商品的描述:2',
            'extra' => ''
        ];
        $url = 'http://test.yunzshop.com/app/index.php?i=2&c=entry&do=shop&m=sz_yi&route=order.testPay';
        //$url = 'http://www.yunzhong.com/app/index.php?i=3&c=entry&do=shop&m=sz_yi&route=order.testPay';
        $data = Curl::to($url)
            ->withData( $query_str )
            ->asJsonResponse()->post();
        //dd($data);exit;

        if(isset($data->data->errno)){
            return $this->errorJson($data->data->message);
        }

        //$data = $pay->doPay(['order_no' => time(), 'amount' => $Order->price, 'subject' => '微信支付', 'body' => '商品的描述:2', 'extra' => '']);
        return $this->successJson($data->data);
    }

    public function alipay()
    {
        //获取支付宝 支付单 数据
    }
}