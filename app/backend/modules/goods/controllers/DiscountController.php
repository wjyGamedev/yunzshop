<?php
/**
 * 商品分享关注操作
 * Created by PhpStorm.
 * User: luckystar_D
 * Date: 2017/2/24
 * Time: 下午3:11
 */

namespace app\backend\modules\goods\controllers;

use app\backend\modules\goods\models\Discount;
use app\common\components\BaseController;
use app\backend\modules\member\models\MemberLevel;
use app\backend\modules\member\models\MemberGroup;

class DiscountController extends BaseController
{
    /**
     * 商品分享关注详情页
     * @return array $item
     */
    public function index()
    {
        $discount = Discount::getList(2);
        $levels = MemberLevel::getMemberLevelList();
        $groups = MemberGroup::getMemberGroupList();
        $this->render('goods/discount/discount', [
            'discount' => $discount,
            'levels' => $levels,
            'groups' => $groups
        ]);
    }

    /**
     * 商品分享关注信息保存
     * @return
     */
    public function save()
    {
        //监听商品添加或编辑操作并获得商品id及相关数据
        $shareInfo = [
            'goods_id' => 0,
            'need_follow' => '0',
            'no_follow_message' => '123123',
            'follow_message' => '123123',
            'share_title' => '123123',
            'share_thumb' => '123123',
            'share_desc' => '123123',
        ];
        $goodsId = $shareInfo['goods_id'];
        $item = Share::getGoodsShareInfo($goodsId);
        if (!empty($item)) {
            //updated
            self::update($shareInfo);
        } else {
            //created
            self::create($shareInfo);
        }
    }

    /**
     * 商品分享关注信息添加方法
     * @return
     */
    public function create($shareInfo)
    {
        if (Share::validator($shareInfo) && Share::createdShare($shareInfo)) {
            echo 1;
        } else {
            echo 2;
        }
    }

    /**
     * 商品分享关注信息更新方法
     * @return
     */
    public function update($shareInfo)
    {
        if (Share::validator($shareInfo) && Share::updatedShare($shareInfo['goods_id'], $shareInfo)) {
            echo 1;
            exit;
        } else {
            echo 2;
            exit;
        }
    }

    /**
     * 商品分享关注信息删除方法
     * @return
     */
    public function delete()
    {
        //监听商品添加或编辑操作并获得商品id及相关数据
        $goodsId = \YunShop::request()->id;
        if (Share::deletedShare($goodsId)) {
            //成功
        } else {
            //失败
        }
    }
}