<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/3/9
 * Time: 上午9:25
 */

namespace app\frontend\modules\goods\services\models;

use app\common\events\OrderGoodsWasAddedInOrder;
use Illuminate\Contracts\Queue\ShouldQueue;

class RealGoodsDispatch
{
    //private $_order_goods_model;
    //todo 待实现
    public function isRealGoods()
    {
        return true;
    }
    //todo 待实现
    public function getDispatchPrice()
    {
        return 12;
    }

    public function handle(OrderGoodsWasAddedInOrder $even)
    {

        //$this->_order_goods_model = $order_goods_model;
        if (!$this->isRealGoods()) {
            return;
        }
        $even->getOrderGoodsModel()->setDispatchPrice($this->getDispatchPrice());

        //$this->_order_goods_model->setDispatchPrice($this->getDispatchPrice());


//        var_dump($this->_order_goods_model);
//        var_dump($even);
//exit;
        return;
    }

}