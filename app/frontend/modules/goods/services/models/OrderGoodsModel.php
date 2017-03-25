<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/3/20
 * Time: 下午3:36
 */

namespace app\frontend\modules\goods\services\models;


abstract class OrderGoodsModel
{
    /**
     * @var \app\frontend\modules\dispatch\services\models\GoodsDispatch 的实例
     */
    protected $_GoodsDispatch;
    /**
     * @var \app\frontend\modules\discount\services\models\GoodsDiscount 的实例
     */
    protected $_GoodsDiscount;
    protected $total;

    public function __construct()
    {
        $this->setGoodsDiscount();
        $this->setGoodsDispatch();
    }

    abstract protected function setGoodsDispatch();

    abstract protected function setGoodsDiscount();

    /**
     * 设置商品数量
     * @param $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }
    /**
     * 计算最终价格
     * @return int
     */
    public function getPrice()
    {
        //最终价格=商品价格+优惠价格
        $result = max($this->getGoodsPrice() + $this->getDiscountPrice(),0);
        return $result;
    }

    /**
     * 计算商品价格
     * @return int
     */
    abstract function getGoodsPrice();

    /**
     * 计算商品优惠价格
     * @return number
     */
    protected function getDiscountPrice()
    {
        return $this->_GoodsDiscount->getDiscountPrice();

    }

    /**
     * 获取订单商品配送详情
     * @return array
     */
    public function getDispatchDetails()
    {
        return $this->_GoodsDispatch->getDispatchDetails();
    }

    /**
     * 获取订单优惠详情
     * @return array
     */
    public function getDiscountDetails()
    {
        return $this->_GoodsDiscount->getDiscountDetails();
    }
}