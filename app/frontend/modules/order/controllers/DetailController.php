<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/3/3
 * Time: 上午9:10
 */

namespace app\frontend\modules\order\controllers;
use app\common\components\BaseController;
use app\common\models\Order;
use Setting;

class DetailController extends BaseController
{
    public function waitPay(){
        $db_order_models = Order::waitPay()->with('hasManyOrderGoods')->first();
        $order = $db_order_models->toArray();
        $this->render('detail', [
            'order' => $order
        ]);
        dd($order);
    }
}