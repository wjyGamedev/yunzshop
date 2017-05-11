<?php
namespace app\common\modules\refund\services;

use app\backend\modules\refund\models\RefundApply;
use app\backend\modules\refund\services\RefundOperationService;
use app\common\exceptions\AdminException;
use app\common\models\finance\Balance;
use app\common\models\PayType;
use app\common\services\PayFactory;
use app\frontend\modules\finance\services\BalanceService;
use Illuminate\Support\Facades\DB;
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2017/5/10
 * Time: 下午4:29
 */
class RefundService
{
    protected $refundApply;

    public function pay($request){
        $this->refundApply = RefundApply::find($request->input('refund_id'));
        if (!isset($this->refundApply)) {
            throw new AdminException('未找到退款记录');
        }
        switch ($this->refundApply->order->pay_type_id) {
            case PayType::WECHAT_PAY:
                $this->wechat();
                break;

            case PayType::ALIPAY:
                $this->alipay();
                break;

            case PayType::CREDIT:
                $this->balance();
                break;

            default:
                break;
        }
    }
    private function wechat()
    {
        $refundApply = $this->refundApply;

        $result = DB::transaction(function () use ($refundApply) {
            //微信退款 同步改变退款和订单状态
            RefundOperationService::refundComplete(['order_id' => $this->refundApply->order->id]);
            $pay = PayFactory::create($this->refundApply->order->pay_type_id);
            
            return $pay->doRefund($this->refundApply->order->hasOneOrderPay->pay_sn, $this->refundApply->order->hasOneOrderPay->amount, $this->refundApply->price);
        });
        if (!$result) {
            throw new AdminException('微信退款失败');
        }
    }

    private function alipay()
    {
        $pay = PayFactory::create($this->refundApply->order->pay_type_id);

        $result = $pay->doRefund($this->refundApply->order->order_sn, $this->refundApply->order->price, $this->refundApply->order->price);
        if (!$result) {
            throw new AdminException('支付宝退款失败');
        }
        //支付宝退款 等待异步通知后,改变退款和订单的状态
    }


    private function balance()
    {
        $refundApply = $this->refundApply;
        $result = DB::transaction(function () use ($refundApply) {
            //退款状态设为完成
            RefundOperationService::refundComplete(['order_id' => $refundApply->order->id]);
            //改变余额
            $data = array(
                'serial_number' => $refundApply->refund_sn,
                'money' => $refundApply->price,
                'remark' => '订单(ID' . $refundApply->order->id . ')余额支付退款(ID' . $refundApply->id . ')' . $refundApply->price,
                'service_type' => Balance::BALANCE_CANCEL_CONSUME,
                'operator' => Balance::OPERATOR_ORDER_,
                'operator_id' => $refundApply->uid,
                'member_id' => $refundApply->uid
            );
            return (new BalanceService())->balanceChange($data);

        });

        if ($result !== true) {
            throw new AdminException($result);
        }

    }
}