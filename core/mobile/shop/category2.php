<?php
/*=============================================================================
#     FileName: category.php
#         Desc: ��Ʒ����
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-13 00:32:05
#      History:
=============================================================================*/

if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation  = !empty($_GPC['op']) ? $_GPC['op'] : 'index';
$openid     = m('user')->getOpenid();
$uniacid    = $_W['uniacid'];
$shopset    = set_medias(m('common')->getSysset('shop'), 'catadvimg');
$commission = p('commission');
if ($commission) {
	$shopid = intval($_GPC['shopid']);
	$shop = set_medias($commission->getShop($openid), array('img', 'logo'));
}
$tcateid = pdo_fetch('select * from ' . tablename('ims_sz_yi_category2') . ' where uniacid=:uniacid and tcate1>0');
if (empty($tcateid)) {
    $shopset['catlevel'] = 2;
}
$this->setHeader();
include $this->template('shop/category2');
