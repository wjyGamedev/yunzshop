<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/2
 * Time: 下午2:03
 */

namespace app\backend\modules\member\controllers;


use app\backend\modules\member\models\McMappingFans;
use app\backend\modules\member\models\Member;
use app\backend\modules\member\models\MemberChildren;
use app\backend\modules\member\models\MemberGroup;
use app\backend\modules\member\models\MemberLevel;
use app\backend\modules\member\models\MemberRecord;
use app\backend\modules\member\models\MemberShopInfo;
use app\backend\modules\member\models\MemberUnique;
use app\backend\modules\member\services\MemberServices;
use app\backend\modules\member\models\MemberParent;
use app\common\components\BaseController;
use app\common\events\member\MemberDelEvent;
use app\common\events\member\MemberRelationEvent;
use app\common\events\member\RegisterByAgent;
use app\common\helpers\Cache;
use app\common\helpers\PaginationHelper;
use app\common\models\AccountWechats;
use app\common\models\member\ChildrenOfMember;
use app\common\models\member\ParentOfMember;
use app\common\models\MemberAlipay;
use app\common\models\MemberMiniAppModel;
use app\common\models\MemberWechatModel;
use app\common\services\ExportService;
use app\common\services\member\MemberRelation;
use app\frontend\modules\member\models\SubMemberModel;
use app\Jobs\ModifyRelationJob;
use Illuminate\Support\Facades\DB;
use Yunshop\Commission\models\Agents;
use Yunshop\TeamDividend\models\TeamDividendLevelModel;


class MemberController extends BaseController
{
    private $pageSize = 20;

    /**
     * 列表
     *
     */
    public function index()
    {
        $groups = MemberGroup::getMemberGroupList();
        $levels = MemberLevel::getMemberLevelList();

        $parames = \YunShop::request();

        if (strpos($parames['search']['searchtime'], '×') !== FALSE) {
            $search_time = explode('×', $parames['search']['searchtime']);

            if (!empty($search_time)) {
                $parames['search']['searchtime'] = $search_time[0];

                $start_time = explode('=', $search_time[1]);
                $end_time = explode('=', $search_time[2]);

                $parames->times = [
                    'start' => $start_time[1],
                    'end' => $end_time[1]
                ];
            }
        }

        $list = Member::searchMembers($parames);

        if ($parames['search']['first_count'] ||
            $parames['search']['second_count'] ||
            $parames['search']['third_count'] ||
            $parames['search']['team_count']
        ) {

            //set_time_limit(0);
            $member_ids = MemberShopInfo::uniacid()->select('member_id')->get();

            $result_ids = [];
            foreach ($member_ids as $key => $member) {

                $is_added = true;
                if ($parames['search']['first_count']) {
                    $first_count = $this->getMembersLower($member->member_id,1);
                    $result_ids = $this->getResultIds($result_ids,$member->member_id,$parames['search']['first_count'],$first_count,$is_added);
                    $is_added = false;
                }
                if ($parames['search']['second_count']) {

                    $second_count = $this->getMembersLower($member->member_id,2);
                    $result_ids = $this->getResultIds($result_ids,$member->member_id,$parames['search']['second_count'],$second_count,$is_added);
                    $is_added = false;
                }
                if ($parames['search']['third_count']) {
                    $third_count = $this->getMembersLower($member->member_id,3);
                    $result_ids = $this->getResultIds($result_ids,$member->member_id,$parames['search']['third_count'],$third_count,$is_added);
                    $is_added = false;
                }
                if ($parames['search']['team_count']) {
                    $team_count = $this->getMemberTeam($member->member_id);
                    $result_ids = $this->getResultIds($result_ids,$member->member_id,$parames['search']['team_count'],$team_count,$is_added);
                }

            }
            $list = $list->whereIn('uid', $result_ids);
        }

        $list = $list->orderBy('uid', 'desc')
            ->paginate($this->pageSize)
            ->toArray();
            
        $set = \Setting::get('shop.member');

        if (empty($set['level_name'])) {
            $set['level_name'] = '普通会员';
        }

        $pager = PaginationHelper::show($list['total'], $list['current_page'], $this->pageSize);

        $starttime = strtotime('-1 month');
        $endtime = time();
        if (isset($parames['search']['searchtime']) &&  $parames['search']['searchtime'] == 1) {
            if ($parames['search']['times']['start'] != '请选择' && $parames['search']['times']['end'] != '请选择') {
                $starttime = strtotime($parames['search']['times']['start']);
                $endtime = strtotime($parames['search']['times']['end']);
            }
        }

        return view('member.index', [
            'list' => $list,
            'levels' => $levels,
            'groups' => $groups,
            'endtime' => $endtime,
            'starttime' => $starttime,
            'total' => $list['current_page'] <= $list['last_page'] ? $list['total'] : 0,
            'pager' => $pager,
            'request' => \YunShop::request(),
            'set' => $set,
            'opencommission' => 1
        ])->render();
    }



    private function getResultIds(array $result_ids, $member_id, $compare, $compared, $is_added)
    {
        if ($compare < $compared) {
            ($is_added && !in_array($member_id, $result_ids)) && $result_ids[] = $member_id;
        } else {
            $key = array_search($member_id, $result_ids);
            $key !== false && array_splice($result_ids,$key,1);
        }
        return $result_ids;
    }

    private function getMembersLower($memberId,$level = '')
    {
        $array      = $level ? [$memberId,$level] : [$memberId];
        $condition  = $level ? ' = ?' : '';
        return MemberShopInfo::select('member_id')->whereRaw('FIND_IN_SET(?,relation)' . $condition, $array)->count();
    }

    private function getMemberTeam($memberId)
    {
        $first = MemberShopInfo::select('member_id','parent_id')->where('parent_id',$memberId)->get();

        $result_ids = [];
        if ($first) {
            foreach($first as $key => $member) {
                $result_ids[] = $member->member_id;
                $second = MemberShopInfo::select('member_id','parent_id')->where('parent_id',$member->member_id)->get();
                if ($second) {
                    $ids = $this->getMemberTeamRecursion($second);
                    $result_ids = array_merge($result_ids,$ids);
                }
            }
        }

        return count($result_ids);
    }

    private function getMemberTeamRecursion($memberIds)
    {
        $result_ids = [];
        foreach($memberIds as $key => $member) {
            $result_ids[] = $member->member_id;
            $first = MemberShopInfo::select('member_id','parent_id')->where('parent_id',$member->member_id)->get();
            if ($first) {
                $ids = $this->getMemberTeamRecursion($first);
                $result_ids = array_merge($result_ids,$ids);
            }
        }

        return $result_ids;
    }


    /**
     * 详情
     *
     */
    public function detail()
    {
        $groups = MemberGroup::getMemberGroupList();
        $levels = MemberLevel::getMemberLevelList();

        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;

        if ($uid == 0 || !is_int($uid)) {
            $this->message('参数错误', '', 'error');
            exit;
        }

        $member = Member::getMemberInfoBlackById($uid);

        if (!empty($member)) {
            $member = $member->toArray();

            if (1 == $member['yz_member']['is_agent'] && 2 == $member['yz_member']['status']) {
                $member['agent'] = 1;
            } else {
                $member['agent'] = 0;
            }

            $myform = json_decode($member['yz_member']['member_form']);
        }

        $set = \Setting::get('shop.member');

        if (empty($set['level_name'])) {
            $set['level_name'] = '普通会员';
        }

        if (0 == $member['yz_member']['parent_id']) {
            $parent_name = '总店';
        } else {
            $parent = Member::getMemberById($member['yz_member']['parent_id']);

            $parent_name = $parent->nickname;
        }

        return view('member.detail', [
            'member' => $member,
            'levels' => $levels,
            'groups' => $groups,
            'set'    => $set,
            'myform' => $myform,
            'parent_name' => $parent_name
        ])->render();
    }

    /**
     * 更新
     *
     */
    public function update()
    {
        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;

        $shopInfoModel = MemberShopInfo::getMemberShopInfo($uid) ?: new MemberShopInfo();

        if ($uid == 0 || !is_int($uid)) {
            $this->message('参数错误', '', 'error');
            exit;
        }

        $parame = \YunShop::request();

        $mc = array(
            'realname' => $parame->data['realname']
        );

        Member::updateMemberInfoById($mc, $uid);

        $yz = array(
            'member_id' => $uid,
            'wechat' => $parame->data['wechat'],
            'parent_id' => $parame->data['parent_id'],
            'uniacid' => \YunShop::app()->uniacid,
            'level_id' => $parame->data['level_id'] ?: 0,
            'group_id' => $parame->data['group_id'],
            'alipayname' => $parame->data['alipayname'],
            'alipay' => $parame->data['alipay'],
            'is_black' => $parame->data['is_black'],
            'content' => $parame->data['content'],
            'custom_value' => $parame->data['custom_value'],
            'validity' => $parame->data['validity'] ? $parame->data['validity'] : 0,
        );

        if ($parame->data['agent']) {
            $yz['is_agent'] = 1;
            $yz['status'] = 2;

            if ($shopInfoModel->inviter == 0) {
                $shopInfoModel->inviter = 1;
                $shopInfoModel->parent_id = 0;
            }

        } else {
            $yz['is_agent'] = 0;
            $yz['status'] =  0;
        }

        $shopInfoModel->fill($yz);
        $validator = $shopInfoModel->validator();
        if ($validator->fails()) {
            $this->error($validator->messages());
        } else {
//            (new \app\common\services\operation\ShopMemberLog($shopInfoModel, 'update'));
            if ($shopInfoModel->save()) {

                if ($parame->data['agent']) {
                    $member = Member::getMemberByUid($uid)->with('hasOneFans')->first();

                    event(new MemberRelationEvent($member));
                }

                return $this->message("用户资料更新成功", yzWebUrl('member.member.index'));
            }
        }
        return $this->message("用户资料更新失败", yzWebUrl('member.member.detail', ['id' => $uid]),'error');
    }

    /**
     * 删除
     *
     */
    public function delete()
    {
        $del = false;

        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;

        if ($uid == 0 || !is_int($uid)) {
            return $this->message('参数错误', '', 'error');
        }

        $member = Member::getMemberBaseInfoById($uid);

        if (empty($member)) {
            return $this->message('用户不存在', '', 'error');
        }


        $del = DB::transaction(function () use ($uid, $member) {
            //商城会员表
            //MemberShopInfo::deleteMemberInfoById($uid);

            //unionid关联表
            if (isset($member->hasOneFans->unionid) && !empty($member->hasOneFans->unionid)) {
                $uniqueModel = MemberUnique::getMemberInfoById($member->hasOneFans->unionid)->first();

                if (!is_null($uniqueModel)) {
                    if ($uniqueModel->member_id != $uid) {
                        //删除会员
                        Member::UpdateDeleteMemberInfoById($uniqueModel->member_id);
                        //小程序会员表
                        MemberMiniAppModel::deleteMemberInfoById($uniqueModel->member_id);
                        //app会员表
                        MemberWechatModel::deleteMemberInfoById($uniqueModel->member_id);

                        //删除微擎mc_mapping_fans 表数据
                        McMappingFans::deleteMemberInfoById($uniqueModel->member_id);

                        //清空 yz_member 关联
                        MemberShopInfo::deleteMemberInfoOpenid($uniqueModel->member_id);
                        //Member::deleteMemberInfoById($uniqueModel->member_id);
                    }
                }
            }

            MemberUnique::deleteMemberInfoById($uid);

            if (app('plugins')->isEnabled('alipay-onekey-login')) {
                //删除支付宝会员表
                MemberAlipay::deleteMemberInfoById($uid);
            }

            //小程序会员表
            MemberMiniAppModel::deleteMemberInfoById($uid);

            //app会员表
            MemberWechatModel::deleteMemberInfoById($uid);

            //删除微擎mc_mapping_fans 表数据
            McMappingFans::deleteMemberInfoById($uid);

            //清空 yz_member 关联
            MemberShopInfo::deleteMemberInfoOpenid($uid);

            //删除会员
            Member::UpdateDeleteMemberInfoById($uid);

            event(new MemberDelEvent($uid));

            return true;
        });

        if ($del) {
            return $this->message('用户删除成功', yzWebUrl('member.member.index'));
        }

        return $this->message('用户删除失败', yzWebUrl('member.member.index'), 'error');
    }

    /**
     * 设置黑名单
     *
     */
    public function black()
    {
        $uid = \YunShop::request()->id ? intval(\YunShop::request()->id) : 0;

        if ($uid == 0 || !is_int($uid)) {
            $this->message('参数错误', '', 'error');
            exit;
        }

        $data = array(
            'is_black' => \YunShop::request()->black
        );

        if (MemberShopInfo::setMemberBlack($uid, $data)) {
            (new \app\common\services\operation\MemberBankCardLog(['uid'=> $uid, 'is_black' => \YunShop::request()->black], 'special'));
            return $this->message('黑名单设置成功', yzWebUrl('member.member.index'));
        } else {
            return $this->message('黑名单设置失败', yzWebUrl('member.member.index'), 'error');
        }
    }

    /**
     * 获取搜索会员
     * @return html
     */
    public function getSearchMember()
    {

        $keyword = \YunShop::request()->keyword;
        $member = Member::getMemberByName($keyword);
        $member = set_medias($member, array('avatar', 'share_icon'));
        return view('member.query', [
            'members' => $member->toArray(),
        ])->render();
    }

    /**
     * 推广下线
     *
     * @return mixed
     */
    public function agentOld()
    {
        $request = \YunShop::request();

        $member_info = Member::getUserInfos($request->id)->first();

        if (empty($member_info)) {
            return $this->message('会员不存在','', 'error');
        }

        $list = Member::getAgentInfoByMemberId($request)
            ->paginate($this->pageSize)
            ->toArray();
//        dd($list);

        $pager = PaginationHelper::show($list['total'], $list['current_page'], $this->pageSize);

        return view('member.agent-old', [
            'member' => $member_info,
            'list'  => $list,
            'pager' => $pager,
            'total' => $list['total'],
            'request' => $request
        ])->render();
    }

    /**
     * 推广下线
     *
     * @return mixed
     */
    public function agent()
    {
        $request = \YunShop::request();

        $member_info = Member::getUserInfos($request->id)->first();

        if (empty($member_info)) {
            return $this->message('会员不存在','', 'error');
        }

        $list = MemberParent::children($request)
            ->paginate($this->pageSize)
            ->toArray();

        dd($list);

        $level_total = MemberParent::where('parent_id', $request->id)
            ->selectRaw('count(member_id) as total, level, max(parent_id) as parent_id')
            ->groupBy('level')
            ->get();

        $pager = PaginationHelper::show($list['total'], $list['current_page'], $this->pageSize);

        return view('member.agent', [
            'member' => $member_info,
            'list'  => $list,
            'pager' => $pager,
            'total' => $list['total'],
            'request' => $request,
            'level_total' => $level_total,
        ])->render();
    }

    public function agentExport()
    {
        $file_name = date('Ymdhis', time()) . '会员下级导出';
        $export_data[0] = ['ID', '昵称', '真实姓名', '电话'];
        $member_id = request()->id;
        $child = MemberParent::where('parent_id', $member_id)->with('hasOneChildMember')->get();
        foreach ($child as $key => $item) {
            $member = $item->hasOneChildMember;
            $export_data[$key + 1] = [
                $member->uid,
                $member->nickname,
                $member->realname,
                $member->mobile,
            ];
        }
        \Excel::create($file_name, function ($excel) use ($export_data) {
            // Set the title
            $excel->setTitle('Office 2005 XLSX Document');

            // Chain the setters
            $excel->setCreator('芸众商城')
                ->setLastModifiedBy("芸众商城")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("report file");

            $excel->sheet('info', function ($sheet) use ($export_data) {
                $sheet->rows($export_data);
            });
        })->export('xls');
    }



    /**
     * 推广上线
     * @throws \Throwable
     */
    public function agentParent()
    {
        $request = \YunShop::request();

        $member_info = Member::getUserInfos($request->id)->first();

        if (empty($member_info)) {
            return $this->message('会员不存在','', 'error');
        }

        $list = MemberParent::parent($request)->orderBy('level','asc')->paginate($this->pageSize)->toArray();

        $pager = PaginationHelper::show($list['total'], $list['current_page'], $this->pageSize);

        return view('member.agent-parent', [
            'member' => $member_info,
            'list'  => $list,
            'pager' => $pager,
            'total' => $list['total'],
            'request' => $request
        ])->render();
    }

    /**
     * 推广上线导出
     */
    public function agentParentExport()
    {
        $file_name = date('Ymdhis', time()) . '会员上级导出';
        $export_data[0] = ['ID', '昵称', '真实姓名', '电话'];
        $member_id = request()->id;

        $child = MemberParent::where('member_id', $member_id)->with(['hasOneMember'])->get();
        foreach ($child as $key => $item) {
            $member = $item->hasOneMember;
            $export_data[$key + 1] = [
                $member->uid,
                $member->nickname,
                $member->realname,
                $member->mobile,
            ];
        }

        \Excel::create($file_name, function ($excel) use ($export_data) {
            // Set the title
            $excel->setTitle('Office 2005 XLSX Document');

            // Chain the setters
            $excel->setCreator('芸众商城')
                ->setLastModifiedBy("芸众商城")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("report file");

            $excel->sheet('info', function ($sheet) use ($export_data) {
                $sheet->rows($export_data);
            });
        })->export('xls');
    }

    public function firstAgentExport()
    {
        $export_data = [];
        $file_name = date('Ymdhis', time()) . '会员直推上级导出';

        $member_id = request()->id;
        $team_list = TeamDividendLevelModel::getList()->get();

        foreach ($team_list as $level) {
            $export_data[0][] = $level->level_name;
            $levelId[] = $level->id;
        }
        array_push($export_data[0], '会员ID', '会员', '姓名/手机号码');

        $child = MemberParent::where('member_id', $member_id)
            ->where('level', 1)
            ->with(['hasManyParent' => function($q) {
                $q->orderBy('level','asc');
            }])
            ->get();

        foreach ($child as $key => $item) {

            $level = $this->getLevel($item, $levelId);

            $export_data[$key + 1] = $level;

            array_push($export_data[$key + 1],
                $item->member_id,
                $item->hasOneMember->nickname,
                $item->hasOneMember->realname . '/' . $item->hasOneMember->mobile);
        }

        \Excel::create($file_name, function ($excel) use ($export_data) {
            // Set the title
            $excel->setTitle('Office 2005 XLSX Document');

            // Chain the setters
            $excel->setCreator('芸众商城')
                ->setLastModifiedBy("芸众商城")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("report file");

            $excel->sheet('info', function ($sheet) use ($export_data) {
                $sheet->rows($export_data);
            });
        })->export('xls');
    }

    public function getLevel($member, $levelId)
    {
        $data = [];
//        $num = count($member->hasManyParentTeam);
        foreach ($levelId as $k => $value) {
            foreach ($member->hasManyParent as $key => $parent) {
                if ($parent->hasOneTeamDividend->hasOneLevel->id == $value) {
                    $data[$k] = $parent->hasOneMember->nickname.' '.$parent->hasOneMember->realname.' '.$parent->hasOneMember->mobile;
                    break;
                }
            }
            $data[$k] = $data[$k] ?: '';
        }

        return $data;
    }

    /**
     * 数据导出
     *
     */
    public function export()
    {
        $member_builder = Member::searchMembers(\YunShop::request());
        $export_page = request()->export_page ? request()->export_page : 1;
        $export_model = new ExportService($member_builder, $export_page);

        $file_name = date('Ymdhis', time()) . '会员导出';

        $export_data[0] = ['会员ID', '粉丝', '姓名', '手机号', '等级', '分组', '注册时间', '积分', '余额', '订单', '金额', '关注', '提现手机号'];

        foreach ($export_model->builder_model->toArray() as $key => $item) {
            if (!empty($item['yz_member']) && !empty($item['yz_member']['group'])) {
                $group = $item['yz_member']['group']['group_name'];

            } else {
                $group = '';
            }

            if (!empty($item['yz_member']) && !empty($item['yz_member']['level'])) {
                $level = $item['yz_member']['level']['level_name'];

            } else {
                $level = '';
            }

            $order = $item['has_one_order']['total']?:0;
            $price = $item['has_one_order']['sum']?:0;

            if (!empty($item['has_one_fans'])) {
                if ($item['has_one_fans']['followed'] == 1) {
                    $fans = '已关注';
                } else {
                    $fans = '未关注';
                }
            } else {
                $fans = '未关注';
            }
            if (substr($item['nickname'], 0, strlen('=')) === '=') {
                $item['nickname'] = '，' . $item['nickname'];
            }

            $export_data[$key + 1] = [$item['uid'], $item['nickname'], $item['realname'], $item['mobile'],
                $level, $group, date('YmdHis', $item['createtime']), $item['credit1'], $item['credit2'], $order,
                $price, $fans, $item['yz_member']['withdraw_mobile']];
        }

        $export_model->export($file_name, $export_data, \Request::query('route'));
    }

    public function search_member()
    {
        $members    = [];
        $parent_id = \YunShop::request()->parent;

        if (is_numeric($parent_id)) {
            $member = Member::getMemberById($parent_id);

            if (!is_null($member)) {
                $members[] = $member->toArray();
            }

            if (0 == $parent_id) {
                $members = 0;
            }
        }

        return view('member.query', [
            'members' => $members
        ])->render();
    }

    public function change_relation()
    {
        $parent_id = \YunShop::request()->parent;
        $uid       = \YunShop::request()->member;

        $msg = MemberShopInfo::change_relation($uid, $parent_id);

        switch ($msg['status']) {
            case -1:
                return $this->message('上线没有推广权限', yzWebUrl('member.member.detail'), 'warning');
                break;
            case 0:
                response(['status' => 0])->send();
                break;
            case 1:
                response(['status' => 1])->send();
                break;
            default:
                response(['status' => 0])->send();
        }
    }

    public function member_record()
    {
        $records = MemberRecord::getRecord(\YunShop::request()->member);

        return view('member.record', [
            'records' => $records
        ])->render();
    }

    public function updateWechatOpenData()
    {
        $status = \YunShop::request()->status;

        if (Cache::has('queque_wechat_total')) {
            Cache::forget('queque_wechat_total');
        }

        if (Cache::has('queque_wechat_page')) {
            Cache::forget('queque_wechat_page');
        }

        if (is_null($status)) {
            $pageSize = 1000;

            $member_info = Member::getQueueAllMembersInfo(\YunShop::app()->uniacid);

            $total       = $member_info->count();
            $total_page  = ceil($total/$pageSize);

            \Log::debug('------total-----', $total);
            \Log::debug('------total_page-----', $total_page);

            Cache::put('queque_wechat_total', $total_page, 30);

            for ($curr_page = 1; $curr_page <= $total_page; $curr_page++) {
                \Log::debug('------curr_page-----', $curr_page);
                $offset      = ($curr_page - 1) * $pageSize;
                $member_info = Member::getQueueAllMembersInfo(\YunShop::app()->uniacid, $pageSize, $offset)->get();
                \Log::debug('------member_count-----', $member_info->count());

                $job = (new \app\Jobs\wechatUnionidJob(\YunShop::app()->uniacid, $member_info));
                dispatch($job);
            }
        } else {
            switch ($status) {
                case 0:
                    return $this->message('微信开放平台数据同步失败', yzWebUrl('member.member.index'), 'error');

                    break;
                case 1:
                    return $this->message('微信开放平台数据同步完成', yzWebUrl('member.member.index'));
                    break;
            }
        }

        return view('member.update-wechat', [])->render();
    }

    public function updateWechatData()
    {
        $total = Cache::get('queque_wechat_total');
        $page  = Cache::get('queque_wechat_page');
\Log::debug('--------ajax total-------', $total);
        \Log::debug('--------ajax page-------', $page);
        if ($total == $page) {
            return json_encode(['status' => 1]);
        } else {
            return json_encode(['status' => 0]);
        }

        /*ry {
            \Artisan::call('syn:wechatUnionid' ,['uniacid'=>$uniacid]);


            return json_encode(['status' => 1]);
        } catch (\Exception $e) {
            return json_encode(['status' => 0]);
        }*/
    }

    public function exportRelation()
    {
        $uniacid = \YunShop::app()->uniacid;
        $parentMemberModle = new ParentOfMember();
        $childMemberModel = new ChildrenOfMember();
        $parentMemberModle->DeletedData($uniacid);
        $childMemberModel->DeletedData($uniacid);

        $member_relation = new MemberRelation();

        $member_relation->createParentOfMember();

        return view('member.export-relation', [])->render();
    }

   
}