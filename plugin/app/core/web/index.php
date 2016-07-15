<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;

$operation   = empty($_GPC['op']) ? 'display' : $_GPC['op'];

$setdata = pdo_fetch("select * from " . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(
    ':uniacid' => $_W['uniacid']
));
$set     = unserialize($setdata['sets']);

$app = $set['app']['base'];

if(!is_array($app)) {
	$app = array();
}
if($_W['ispost']) {
	//app
	$app = array_elements(array('switch', 'accept', 'useing', 'android_url', 'ios_url'), $_GPC['app']);

	$set['app']['base'] = $app;

	$leancloud = array_elements(array('id', 'key', 'master', 'notify'), $_GPC['leancloud']);
	$set['app']['base']['leancloud'] = $leancloud;

    $setdata = pdo_fetch("select * from " . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(
        ':uniacid' => $_W['uniacid']
    ));

    if(pdo_update('sz_yi_sysset', array('sets' => iserializer($set)), array('uniacid' => $_W['uniacid'])) !== false) {
        m('cache')->set('sysset', $setdata);
		message('保存设置信息成功. ', 'refresh');
	} else {
		message('保存设置信息失败, 请稍后重试. ');
	}
	exit();
}

load()->func('tpl');
include $this->template('index');
