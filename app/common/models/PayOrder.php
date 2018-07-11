<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/20
 * Time: 上午10:42
 */

namespace app\common\models;

use app\backend\models\BackendModel;
use Illuminate\Support\Collection;

/**
 * Class PayOrder
 * @package app\common\models
 * @property int status
 * @property Collection all_status
 * @property string status_name
 */
class PayOrder extends BackendModel
{
    public $table = 'yz_pay_order';
    const STATUS_UNPAID = 0;
    const STATUS_WAIT_PAID = 1;
    const STATUS_PAID = 2;
    protected $appends = ['status_name'];
    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = ['uniacid', 'member_id', 'int_order_no', 'out_order_no', 'status', 'type', 'third_type', 'price'];

    public static function getPayOrderInfo($orderno)
    {
        return self::uniacid()
            ->where('out_order_no', $orderno)
            ->orderBy('id', 'desc');
    }

    public static function getPayOrderInfoByTradeNo($trade_no)
    {
        return self::uniacid()
            ->where('trade_no', $trade_no)
            ->orderBy('id', 'desc');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllStatusAttribute(){
        return collect([
            self::STATUS_UNPAID => '未支付',
            self::STATUS_WAIT_PAID => '待支付',
            self::STATUS_PAID => '已支付',
        ]);
    }

    /**
     * @return mixed
     */
    public function getStatusNameAttribute()
    {
        return $this->all_status[$this->status];
    }
}