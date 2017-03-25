<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/3/21
 * Time: 上午9:48
 */

namespace app\frontend\modules\order\services\models;


use app\frontend\modules\discount\services\models\OrderDiscount;
abstract class OrderModel
{
    /**
     * @var array 未插入数据库的订单商品数组
     */
    protected $_OrderGoodsModels = [];

    /**
     * @var \app\frontend\modules\dispatch\services\models\OrderDispatch 运费类实例
     */
    protected $_OrderDispatch;
    /**
     * @var OrderDiscount 优惠类实例
     */
    protected $_OrderDiscount;

    public function __construct($OrderGoodsModels)
    {
        $this->setOrderGoodsModels($OrderGoodsModels);
        $this->setDispatch();
        $this->setDiscount();
    }
    abstract protected function setDispatch();

    abstract protected function setDiscount();

    abstract protected function setOrderGoodsModels(array $OrderGoodsModels);

    /**
     * 统计商品总数
     * @return int
     */
    protected function getGoodsTotal()
    {
        //累加所有商品数量
        $result = 0;
        foreach ($this->_OrderGoodsModels as $pre_order_goods_model) {
            $result += $pre_order_goods_model->getTotal();
        }
        return $result;
    }

    /**
     * 计算订单优惠
     * @return number
     */
    protected function getDiscountPrice(){
        return $this->_OrderDiscount->getDiscountPrice();
    }

    /**
     * 计算订单运费
     * @return int|number
     */
    protected function getDispatchPrice(){
        return $this->_OrderDispatch->getDispatchPrice();
    }

    /**
     * 计算订单最终价格
     * @return int
     */
    protected function getPrice()
    {
        //订单最终价格 = 商品最终价格 + 订单优惠 + 订单运费
        return max($this->getGoodsPrice() + $this->getDiscountPrice() + $this->getDispatchPrice(),0);
    }

    /**
     * 统计订单商品最终价格
     * @return int
     */
    protected function getGoodsPrice()
    {
        //dd($this->_OrderGoodsModels);exit;
        //累加所有商品最终价格
        $result = 0;
        foreach ($this->_OrderGoodsModels as $OrderGoodsModel) {
            $result += $OrderGoodsModel->getPrice();
        }
        return $result;
    }
    /**
     * 属性获取器
     * todo 准备删除这个方法
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        return null;
    }

}