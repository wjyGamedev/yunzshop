<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2018/10/15
 * Time: 16:32
 */

namespace app\backend\modules\charts\models;


use Yunshop\Supplier\common\models\SupplierWithdraw;
use Yunshop\Supplier\supplier\models\SupplierOrder;

class Supplier extends \Yunshop\Supplier\common\models\Supplier
{

    public function hasOneSupplierOrder()
    {
        return $this->hasOne(SupplierOrder::class, 'supplier_id', 'id'); // TODO: Change the autogenerated stub
    }

    public function hasManySupplierOrderCount()
    {
        return $this->hasMany(SupplierOrder::class, 'supplier_id', 'id'); // TODO: Change the autogenerated stub
    }

    public function hasOneSupplierWithdraw()
    {
        return $this->hasOne(SupplierWithdraw::class, 'supplier_id', 'id'); // TODO: Change the autogenerated stub
    }

    public function hasManyOrder()
    {
        return $this->belongsToMany(\app\common\models\Order::class,'yz_supplier_order', 'supplier_id', 'order_id');
    }
}