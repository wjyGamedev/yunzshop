<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2017/4/11
 * Time: 下午9:44
 */

namespace app\frontend\modules\finance\services;

use app\backend\modules\member\models\Member;
use Setting;

class CalculationPointService
{
    private $orderGoodsModels;
    private $point_set;
    public $point;
    private $member;
    public $point_money;

    public function __construct($orderGoodsModels, $member_id)
    {
        $this->verifyPointSet();
        $this->vetifyMemberPoint($member_id);
        $this->orderGoodsModels = $orderGoodsModels;
        $this->calculationPoint();
        $this->point_money = $this->point * $this->point_set['money'];
    }

    /**
     * @name 验证积分设置的是否开启积分抵扣
     * @author yangyang
     * @return false
     */
    private function verifyPointSet()
    {
        if (Setting::get('point.set')['point_deduct'] == 0) {
            return false;
        }
        $this->point_set = Setting::get('point.set');
    }

    /**
     * @name 验证用户是否有积分
     * @author yangyang
     * @param $member_id
     * @return false
     */
    private function vetifyMemberPoint($member_id)
    {
        if (Member::getMemberInfoById($member_id)['credit1'] <= 0) {
            return false;
        }
        $this->member = Member::getMemberInfoById($member_id);
    }

    /**
     * @name 计算可以使用多少积分
     * @author yangyang
     */
    private function calculationPoint()
    {
        foreach ($this->orderGoodsModels as $goodsModel) {
            $this->calculationMemberPoint($this->getGoodsPoint($goodsModel));
        }
    }

    /**
     * @name 获取商品
     * @author yangyang
     * @param $goods_model
     * @return float|int
     */
    private function getGoodsPoint($goods_model)
    {

        if ($goods_model->hasOneSale->max_point_deduct > 0) {
            $goods_point = $goods_model->hasOneSale->max_point_deduct / $this->point_set['money'];
            return $goods_point;
        } else if ($this->point_set['money_max'] > 0) {
            $goods_point = $this->point_set['money_max'] / 100 * $goods_model->price / $this->point_set['money'];
            return $goods_point;
        }
    }

    private function calculationMemberPoint($goods_point)
    {
        if ($goods_point >= $this->member['credit1']) {
            $this->point += $this->member['credit1'];
            $this->member['credit1'] = 0;
        } else {
            $this->point += $goods_point;
            $this->member['credit1'] = $this->member['credit1'] - $goods_point;
        }
    }
}