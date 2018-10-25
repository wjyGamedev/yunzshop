<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2018/10/16
 * Time: 11:51
 */
namespace app\backend\modules\charts\modules\income\controllers;

use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\models\order\OrderPluginBonus;

class ShopIncomeListController extends BaseController
{
    /**
     * @return string
     * @throws \Throwable
     */
    public function index()
    {
        $pageSize = 10;
        $search = \YunShop::request()->search;
        $list = OrderPluginBonus::search($search)
            ->selectRaw('sum(undividend) as undividend, order_id, max(if(code like "order_sn",content,0)) as order_sn')
            ->selectRaw('max(price) as price, order_id, max(if(code like "shop_name",content,0)) as shop_name')
            ->selectRaw('max(if(code like "buy_name",content,0)) as buy_name')
            ->groupBy('order_id')
            ->with([
                'hasOneOrderGoods' => function($q) {
                    $q->selectRaw('sum(goods_cost_price) as cost_price, order_id')->groupBy('order_id');
                },
                'hasOneStoreOrder',
                'hasOneSupplierOrder',
                'hasOneCashierOrder',
            ])
            ->orderBy('order_id', 'desc')
            ->paginate($pageSize);

        $pager = PaginationHelper::show($list->total(), $list->currentPage(), $list->perPage());
        return view('charts.income.shop_income_list',[
            'list' => $list,
            'pager' => $pager,
            'search' => $search,
        ])->render();
    }

}