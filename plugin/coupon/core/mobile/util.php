<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
	exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'query';
$openid = m('user')->getOpenid();
if ($operation == 'query') {
	$type = intval($_GPC['type']);
	$cashier= intval($_GPC['cashier']);
	$sid = intval($_GPC['sid']);
	$supplier_uid = intval($_GPC['supplier_uid']);
	$money = floatval($_GPC['money']);
	$time = time();
	if ($cashier == 1) {
		$sql = 'select d.id,d.couponid,d.gettime,c.timelimit,c.timedays,c.timestart,c.timeend,c.thumb,c.couponname,c.enough,c.backtype,c.deduct,c.discount,c.backmoney,c.backcredit,c.backredpack,c.bgcolor,c.thumb,c.supplier_uid,c.cashiersids from ' . tablename('sz_yi_coupon_data') . ' d';
		$sql .= ' left join ' . tablename('sz_yi_coupon') . ' c on d.couponid = c.id';
		$sql .= " where c.supplier_uid=0 and d.openid=:openid and d.uniacid=:uniacid and c.getcashier=1 and  c.coupontype={$type} and {$money}>=c.enough and d.used=0 ";
		$sql .= " and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<={$time} && c.timeend>={$time})) order by d.gettime desc";
		$list = set_medias(pdo_fetchall($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid'])), 'thumb');
		foreach ($list as $key => &$row) {
			$cashierids = unserialize($row['cashiersids']);
			if (!empty($cashierids)) {
				$a = 0;
				foreach ($cashierids as $value) {
					if ($value == $sid) {
						$a += 1;
					}
				}
				if ($a == 0) {
					unset($list[$key]);
				}
			}

			$row['thumb'] = tomedia($row['thumb']);
			$row['timestr'] = '永久有效';
			if (empty($row['timelimit'])) {
				if (!empty($row['timedays'])) {
					$row['timestr'] = date('Y-m-d H:i', $row['gettime'] + $row['timedays'] * 86400);
				}
			} else {
				if ($row['timestart'] >= $time) {
					$row['timestr'] = date('Y-m-d H:i', $row['timestart']) . '-' . date('Y-m-d H:i', $row['timeend']);
				} else {
					$row['timestr'] = date('Y-m-d H:i', $row['timeend']);
				}
			}
			if ($row['backtype'] == 0) {
				$row['backstr'] = '立减';
				$row['css'] = 'deduct';
				$row['backmoney'] = $row['deduct'];
				$row['backpre'] = true;
			} else if ($row['backtype'] == 1) {
				$row['backstr'] = '折';
				$row['css'] = 'discount';
				$row['backmoney'] = $row['discount'];
			} else if ($row['backtype'] == 2) {
				if ($row['backredpack'] > 0) {
					$row['backstr'] = '返现';
					$row['css'] = 'redpack';
					$row['backmoney'] = $row['backredpack'];
					$row['backpre'] = true;
				} else if ($row['backmoney'] > 0) {
					$row['backstr'] = '返利';
					$row['css'] = 'money';
					$row['backmoney'] = $row['backmoney'];
					$row['backpre'] = true;
				} else if (!empty($row['backcredit'])) {
					$row['backstr'] = '返积分';
					$row['css'] = 'credit';
					$row['backmoney'] = $row['backcredit'];
				}
			}
		}
	} else {
		$sql = 'select d.id,d.couponid,d.gettime,c.timelimit,c.timedays,c.timestart,c.timeend,c.thumb,c.couponname,c.enough,c.backtype,c.deduct,c.discount,c.backmoney,c.backcredit,c.backredpack,c.bgcolor,c.thumb,c.supplier_uid,c.usetype,c.goodsids,c.categoryids,c.storeids,c.getstore from ' . tablename('sz_yi_coupon_data') . ' d';
		$sql .= ' left join ' . tablename('sz_yi_coupon') . ' c on d.couponid = c.id';
		$sql .= " where c.supplier_uid=0 and d.openid=:openid and d.uniacid=:uniacid and c.getcashier=0 and c.coupontype={$type} and {$money}>=c.enough and d.used=0 ";
		$sql .= " and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<={$time} && c.timeend>={$time})) order by d.gettime desc";
		$list = set_medias(pdo_fetchall($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid'])), 'thumb');
		if (!empty($_GPC['cartids'])) {
			$cartids = $_GPC['cartids'];
		}
		if (!empty($_GPC['goodsid'])) {
			$goodsid = intval($_GPC['goodsid']);
		}
		
		$carrierid = $_GPC['carrierid'] ? intval($_GPC['carrierid']) : 0;
		
		foreach ($list as $key => &$row) {
			$storeids = unserialize($row['storeids']);
			if ($goodsid) {
				if ($row['usetype'] == 0) {
					if ($row['getstore'] == 1) {
						if ($carrierid != 0) {
							foreach ($storeids as $vs) {
								if ($vs == $carrierid) {
									$b += 1;
								}
							}
							if ($b == 0) {
								unset($list[$key]);
							}
						} else {
							unset($list[$key]);
						}
						
					}
				} elseif ($row['usetype'] == 2) {
					$goodsids = unserialize($row['goodsids']);
					$a = 0;
					$b = 0;
					foreach ($goodsids as $value) {
						if ($value == $goodsid) {
							$a += 1;
						}
					}
					if ($row['getstore'] == 1) {
						if ($carrierid != 0) {
							foreach ($storeids as $vs) {
								if ($vs == $carrierid) {
									$b += 1;
								}
							}
							if ($a == 0 || $b == 0) {
								unset($list[$key]);
							}
						} else {
							unset($list[$key]);
						}
					} else {
						if ($a == 0) {
							unset($list[$key]);
						}
					}
					
				} elseif ($row['usetype'] == 1){
					$categoryids = unserialize($row['categoryids']);
					$goods = pdo_fetch(" SELECT * FROM ".tablename('sz_yi_goods')." WHERE id = :id",array(':id' => $goodsid));
					$a = 0;
					$b = 0;
					foreach ($categoryids as $v) {
						if ($v == $goods['ccate'] || $v == $goods['tcate'] ) {
							$b += 1;
						}
					}
					if ($row['getstore'] == 1) {
						if ($carrierid != 0) {
							foreach ($storeids as $vs) {
								if ($vs == $carrierid) {
									$b += 1;
								}
							}
							if ($a == 0 || $b == 0) {
								unset($list[$key]);
							}
						} else {
							unset($list[$key]);
						}
					} else {
						if ($a == 0) {
							unset($list[$key]);
						}
					}
					
				}
			} elseif ($cartids) {
				if($row['usetype'] == 2){
					$goodsids = unserialize($row['goodsids']);
					$cartid = explode(',',$cartids);
					$a = 0;
					foreach ($cartid as $value) {
						$gid = pdo_fetchcolumn("SELECT goodsid FROM ".tablename('sz_yi_member_cart')." WHERE id=:id ",array(':id' => $value));
						foreach ($goodsids as $v) {
							if($v == $gid){
								$a += 1;
							}
						}
					}
					if($a == 0){
						unset($list[$key]);
					}	
				} elseif ($row['usetype'] == 1) {
					$categoryids = unserialize($row['categoryids']);
					$cartid = explode(',',$cartids);
					$b = 0;
					foreach ($categoryids as $v) {
						foreach ($cartid as $vc) {
							$gid = pdo_fetchcolumn("SELECT goodsid FROM ".tablename('sz_yi_member_cart')." WHERE id=:id ",array(':id' => $vc));
							$goods = pdo_fetch(" SELECT * FROM ".tablename('sz_yi_goods')." WHERE id = :id",array(':id' => $gid));
							if ($v == $goods['ccate'] || $v == $goods['tcate'] ) {
								$b += 1;
							}	
						}
						
					}
					if ($b == 0) {
						unset($list[$key]);
					}
				}
				
			}
			$row['thumb'] = tomedia($row['thumb']);
			$row['timestr'] = '永久有效';
			if (empty($row['timelimit'])) {
				if (!empty($row['timedays'])) {
					$row['timestr'] = date('Y-m-d H:i', $row['gettime'] + $row['timedays'] * 86400);
				}
			} else {
				if ($row['timestart'] >= $time) {
					$row['timestr'] = date('Y-m-d H:i', $row['timestart']) . '-' . date('Y-m-d H:i', $row['timeend']);
				} else {
					$row['timestr'] = date('Y-m-d H:i', $row['timeend']);
				}
			}
			if ($row['backtype'] == 0) {
				$row['backstr'] = '立减';
				$row['css'] = 'deduct';
				$row['backmoney'] = $row['deduct'];
				$row['backpre'] = true;
			} else if ($row['backtype'] == 1) {
				$row['backstr'] = '折';
				$row['css'] = 'discount';
				$row['backmoney'] = $row['discount'];
			} else if ($row['backtype'] == 2) {
				if ($row['backredpack'] > 0) {
					$row['backstr'] = '返现';
					$row['css'] = 'redpack';
					$row['backmoney'] = $row['backredpack'];
					$row['backpre'] = true;
				} else if ($row['backmoney'] > 0) {
					$row['backstr'] = '返利';
					$row['css'] = 'money';
					$row['backmoney'] = $row['backmoney'];
					$row['backpre'] = true;
				} else if (!empty($row['backcredit'])) {
					$row['backstr'] = '返积分';
					$row['css'] = 'credit';
					$row['backmoney'] = $row['backcredit'];
				}
			}
		}
	}
	
	
	unset($row);
	show_json(1, array('coupons' => $list, 'supplier_uid' => $supplier_uid));
}
