<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 17/2/22
 * Time: 下午4:44
 */

namespace app\frontend\modules\member\services;

use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\AccountWechats;
use app\common\models\Member;
use app\common\models\MemberGroup;
use app\common\models\MemberShopInfo;
use app\common\services\Session;
use app\frontend\models\McGroupsModel;
use app\frontend\modules\member\models\McMappingFansModel;
use app\frontend\modules\member\models\MemberModel;
use app\frontend\modules\member\models\MemberUniqueModel;
use app\frontend\modules\member\models\SubMemberModel;

class MemberOfficeAccountService extends MemberService
{
    const LOGIN_TYPE = '1';

    public function __construct()
    {
    }

    public function login($params = [])
    {
        $member_id = 0;

        $uniacid = \YunShop::app()->uniacid;
        $code = \YunShop::request()->code;

        $account = AccountWechats::getAccountByUniacid($uniacid);
        $appId = $account->key;
        $appSecret = $account->secret;

        if ($params['scope'] == 'user_info') {
            $callback = Url::absoluteApi('member.login.index', ['type' => 1, 'scope' => 'user_info']);


        } else {
            $callback = ($_SERVER['REQUEST_SCHEME'] ? $_SERVER['REQUEST_SCHEME'] : 'http')  . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        }

        $state = 'yz-' . session_id();

        if (!Session::get('member_id')) {
            if ($params['scope'] == 'user_info' || \YunShop::request()->scope == 'user_info') {
                $authurl = $this->_getAuthBaseUrl($appId, $callback, $state);
            } else {
                $authurl = $this->_getAuthUrl($appId, $callback, $state);
            }
        } else {
            $authurl = $this->_getAuthBaseUrl($appId, $callback, $state);
        }

        $tokenurl = $this->_getTokenUrl($appId, $appSecret, $code);

        if (!empty($code)) {
            $redirect_url = $this->_getClientRequestUrl();

            $token = \Curl::to($tokenurl)
                ->asJsonResponse(true)
                ->get();

            if (!empty($token) && !empty($token['errmsg']) && $token['errmsg'] == 'invalid code') {
                return show_json(5, 'token请求错误');
            }

            $userinfo = $this->getUserInfo($appId, $appSecret, $token);

            if (is_array($userinfo) && !empty($userinfo['errcode'])) {
                \Log::debug('微信登陆授权失败');
                return show_json('-3', '微信登陆授权失败');
            }

            //Login
            $member_id = $this->memberLogin($userinfo);

            \YunShop::app()->openid = $userinfo['openid'];

            Session::set('member_id', $member_id);
        } else {
            $this->_setClientRequestUrl();

            redirect($authurl)->send();
            exit;
        }

        if (\YunShop::request()->scope == 'user_info') {
            return show_json(1, 'user_info_api');
        } else {
            redirect($redirect_url)->send();
        }
    }

    /**
     * 获取用户信息
     *
     * @param $appId
     * @param $appSecret
     * @param $token
     * @return mixed
     */
    public function getUserInfo($appId, $appSecret, $token)
    {
        $global_access_token_url = $this->_getAccessToken($appId, $appSecret);

        $global_token = \Curl::to($global_access_token_url)
            ->asJsonResponse(true)
            ->get();

        $global_userinfo_url = $this->_getInfo($global_token['access_token'], $token['openid']);

        $user_info = \Curl::to($global_userinfo_url)
            ->asJsonResponse(true)
            ->get();

        if (0 == $user_info['subscribe']) {
            $userinfo_url = $this->_getUserInfoUrl($token['access_token'], $token['openid']);

            $user_info = \Curl::to($userinfo_url)
                ->asJsonResponse(true)
                ->get();

            $user_info['subscribe'] = 0;
        }

        return $user_info;
    }

    /**
     * 用户验证授权 api
     *
     * snsapi_userinfo
     *
     * @param $appId
     * @param $url
     * @param $state
     * @return string
     */
    private function _getAuthUrl($appId, $url, $state)
    {
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appId . "&redirect_uri=" . urlencode($url) . "&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
    }

    /**
     *
     * 静默获取用户信息
     *
     * snsapi_base
     *
     * @param $appId
     * @param $url
     * @param $state
     * @return string
     */
    private function _getAuthBaseUrl($appId, $url, $state)
    {
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appId . "&redirect_uri=" . urlencode($url) . "&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
    }

    /**
     * 获取token api
     *
     * @param $appId
     * @param $appSecret
     * @param $code
     * @return string
     */
    private function _getTokenUrl($appId, $appSecret, $code)
    {
        return "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appId . "&secret=" . $appSecret . "&code=" . $code . "&grant_type=authorization_code";
    }

    /**
     * 获取用户信息 api
     * @param $accesstoken
     * @param $openid
     * @return string
     */
    private function _getUserInfoUrl($accesstoken, $openid)
    {
        return "https://api.weixin.qq.com/sns/userinfo?access_token={$accesstoken}&openid={$openid}&lang=zh_CN";
    }

    /**
     * 获取全局ACCESS TOKEN
     * @return string
     */
    private function _getAccessToken($appId, $appSecret)
    {
        return 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appId . '&secret=' . $appSecret;
    }

    /**
     * 获取用户信息
     *
     * 是否关注公众号
     *
     * @param $accesstoken
     * @param $openid
     * @return string
     */
    private function _getInfo($accesstoken, $openid)
    {
        return 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $accesstoken . '&openid=' . $openid;
    }

    /**
     * 设置客户端请求地址
     *
     * @return string
     */
    private function _setClientRequestUrl()
    {
        if (\YunShop::request()->yz_redirect) {
            Session::set('client_url', base64_decode(\YunShop::request()->yz_redirect));
        } else {
            Session::set('client_url', '');
        }
    }

    /**
     * 获取客户端地址
     *
     * @return mixed
     */
    private function _getClientRequestUrl()
    {
        return Session::get('client_url');
    }
}
