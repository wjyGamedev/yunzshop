<?php


global $_W, $_GPC;

ca('diyform.set.view');
$set       = $this->getSet();
$use_form_list = $this->model->getDiyformList();
$supplier_form_list = array();
if (p('supplier')) {
	foreach ($use_form_list as $key => $value) {
		$value['fields'] = unserialize($value['fields']);
		foreach ($value['fields'] as $val) {
			if ($val['tp_is_default'] == 5 || $val['tp_is_default'] == 6) {
				unset($use_form_list[$key]);
				$supplier_form_list[] = $value;
				break;
			}
		}
	}
}

if (checksubmit('submit')) {
    ca('diyform.set.save');
    $data = is_array($_GPC['setdata']) ? array_merge($set, $_GPC['setdata']) : array();
    $this->updateSet($data);
    plog('diyform.set.save', '修改基本设置');
    message('设置保存成功!', referer(), 'success');
}
load()->func('tpl');
include $this->template('set');
