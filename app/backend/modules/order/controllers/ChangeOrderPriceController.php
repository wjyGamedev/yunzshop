<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/3/18
 * Time: 上午10:00
 */

namespace app\backend\modules\order\controllers;

use app\backend\modules\order\models\Order;
use app\common\components\BaseController;
use app\common\models\order\OrderChangePriceLog;
use app\common\models\OrderGoods;
use app\frontend\modules\order\services\OrderService;
use Illuminate\Support\Facades\DB;

class ChangeOrderPriceController extends BaseController
{

    public function index()
    {
        $order_model = Order::find(\YunShop::request()->order_id);
        return view('order.change_price',[
            'order_goods_model' => $order_model->hasManyOrderGoods,
            'order_model'       => $order_model,
            'change_num'        => 1//改价次数
        ]);
    }

    public function store(\Request $request)
    {
        //dd(\YunShop::app()->user->name);
        list($result, $message) = OrderService::changeOrderPrice($request);
        if ($result === false) {
            return $this->errorJson($message);
        }
        return $this->successJson($message);
    }

    public function back(\Request $request){
        $orderId = $request->input('order_id');
        $this->validate($request,[
            'order_id'=>'required'
        ]);
        $order = Order::find($orderId);
        $change_price = $order->orderChangePriceLogs->sum('change_price');

        $order->price -= $change_price;
        $order->order_goods_price -= $change_price;
        $order->dispatch -= $order->orderChangePriceLogs->sum('change_dispatch_price');
        DB::transaction(function ()use ($order){
            $order->hasManyOrderGoods->sum(function ($orderGoods){
                dd($orderGoods->hasManyChangeOrderGoodsPrcieLogs);
                exit;
                if(!isset($orderGoods->hasManyChangeOrderGoodsPrcieLogs)){
                    return 0;
                }
                $result = $orderGoods->hasManyChangeOrderGoodsPrcieLogs->sum('change_price');
                /**
                 * @var $orderGoods OrderGoods
                 */
                $orderGoods->orderGoodsChangePriceLogs()->delete();
                return $result;
            });
            $order->orderChangePriceLogs()->delete();
            $order->push();
        });

        echo 'ok';
    }
}