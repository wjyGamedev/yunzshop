<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/7/9
 * Time: 下午9:40
 */

namespace app\framework\Http;


use Illuminate\Support\Str;

class Request extends \Illuminate\Http\Request
{
    public function isBackend()
    {
        if (strpos(request()->getRequestUri(), '/web/') !== false) {
            return true;
        }
        return false;
    }

    public function isShop()
    {
        return !$this->isPlugins();
    }

    public function isFrontend()
    {
        if (strpos(request()->getRequestUri(), '/addons/') !== false
            && strpos(request()->getRequestUri(), '/api.php') !== false
        ) {
            return true;
        }
        return false;
    }

    public function isPayment()
    {
        return strpos($_SERVER['PHP_SELF'], '/payment/') > 0 ? true : false;
    }

    public function isPlugins()
    {
        return Str::startsWith(request('route'), 'plugin.');
    }

    public function isCron()
    {
        return strpos(request()->getRequestUri(), '/addons/') !== false &&
            strpos(request()->getRequestUri(), '/cron.php') !== false;
    }
}