<?php
namespace app\frontend\modules\coupon\services\models\TimeLimit;

/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/3/29
 * Time: 下午5:14
 */
class DateTimeRange extends TimeLimit
{
    public function valid()
    {
        if(time() < $this->dbCoupon->time_start){
            //未开始
            return false;
        }
        if(time() > $this->dbCoupon->time_end){
            //已结束
            return false;
        }

        return true;
    }
}