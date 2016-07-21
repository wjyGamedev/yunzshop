<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 16/7/20
 * Time: 下午4:56
 */

global $_W, $_GPC;

$uniacid   = $_W['uniacid'];

//轮播图
$advs = pdo_fetchall('select id,advname,link,thumb,thumb_pc from ' . tablename('sz_yi_adv') . ' where uniacid=:uniacid and enabled=1 order by displayorder desc', array(':uniacid' => $uniacid));
foreach($advs as $key => $adv){
    if(!empty($advs[$key]['thumb'])){
        $adv[] = $advs[$key];
    }
    if(!empty($advs[$key]['thumb_pc'])){
        $adv_pc[] = $advs[$key];
    }
}
$advs = set_medias($advs, 'thumb,thumb_pc');

//推荐分类
$category = pdo_fetchall('select id,name,thumb,parentid,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and ishome=1 and enabled=1 order by displayorder desc', array(':uniacid' => $uniacid));
$category = set_medias($category, 'thumb');

foreach ($category as &$c) {
    $c['thumb'] = tomedia($c['thumb']);
    if ($c['level'] == 3) {
        $c['url'] = $this->createMobileUrl('shop/list', array('tcate' => $c['id']));
    } else if ($c['level'] == 2) {
        $c['url'] = $this->createMobileUrl('shop/list', array('ccate' => $c['id']));
    }
}

//推荐宝贝
$args = array('page' => $_GPC['page'], 'pagesize' => 6, 'isrecommand' => 1, 'order' => 'displayorder desc,createtime desc', 'by' => '');
$goods = m('goods')->getList($args);

//echo '<pre>';print_r($advs);exit;
$app_interface = new InterfaceController();
$res = array(
    'advs' => $advs,
    'category' => $category,
    'goods' => $goods
);
$app_interface->checkResultAndReturn($res);
