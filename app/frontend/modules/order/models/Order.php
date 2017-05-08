<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/4/6
 * Time: 下午4:35
 */
namespace app\frontend\modules\order\models;

use app\frontend\models\Member;
use Illuminate\Database\Eloquent\Builder;

class Order extends \app\common\models\Order
{
    protected $appends = ['status_name', 'pay_type_name', 'button_models'];
    protected $hidden = [
        'uniacid',
        'create_time',
        'is_deleted',
        'is_member_deleted',
        'finish_time',
        'pay_time',
        'send_time',
        'send_time',
        'uid',
        'cancel_time',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * 订单列表
     * @param $uid
     * @return $this
     */
    public static function orders()
    {
        $orders = self::with(['hasManyOrderGoods'=>function($query){
            return $query->select(['order_id','goods_id','goods_price','total','price','thumb','title','goods_option_id','goods_option_title','comment_status']);
        }])->orderBy('id','desc');
        return $orders;
    }
    public function belongsToMember()
    {
        return $this->belongsTo(Member::class, 'uid', 'uid');
    }

    public function belongsToOrderGoods()
    {
        return $this->belongsTo(\app\common\models\OrderGoods::class, 'id', 'order_id');
    }

    public function scopeOrders($query)
    {
        return $query->with('hasManyOrderGoods');
    }

    public function orderGoodsBuilder($status)
    {
        $operator = [];
        if ($status == 0) {
            $operator['operator'] = '=';
            $operator['status'] = 0;
        } else {
            $operator['operator'] = '>';
            $operator['status'] = 0;
        }
        return function ($query) use ($operator) {
            return $query->with('hasOneComment')->where('comment_status', $operator['operator'], $operator['status']);
        };
    }

    public static function getMyCommentList($status)
    {
        $operator = [];
        if ($status == 0) {
            $operator['operator'] = '=';
            $operator['status'] = 0;
        } else {
            $operator['operator'] = '>';
            $operator['status'] = 0;
        }
        return self::whereHas('hasManyOrderGoods', function($query) use ($operator){
                return $query->where('comment_status', $operator['operator'], $operator['status']);
            })
            ->with([
                'hasManyOrderGoods' => self::orderGoodsBuilder($status)
            ])->where('status', 3)->orderBy('id', 'desc')->get();
    }

    /**
     * 关系链 指定商品
     *
     * @param $uid
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getOrderListByUid($uid)
    {
        return self::getOrderList($uid)
            ->where('status','>=',1)
            ->where('status','<=',3)
            ->get();
    }


    public static function boot()
    {
        parent::boot();

        self::addGlobalScope(function(Builder $query){
            return $query->where('uid', \YunShop::app()->getMemberId());
        });
    }
}