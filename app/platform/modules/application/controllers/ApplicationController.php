<?php

namespace app\platform\modules\application\controllers;

use app\platform\controllers\BaseController;
use app\platform\modules\application\models\UniacidApp;
use app\common\helpers\Cache;

class ApplicationController extends BaseController
{
    protected $key = 'application';

    public function index()
    {
        $search = request()->search;
        
        $app = new UniacidApp();

        if ($search) {
            
            $app = $app->search($search);

        } 
            $list = $app->orderBy('id', 'desc')->paginate()->toArray();


        return $this->successJson('获取成功',  $list);
    }

    public function add()
    {
        $app = new UniacidApp();

        $data = $this->fillData(request()->input());

        $app->fill($data);

        $validator = $app->validator();

        if ($validator->fails()) {

            return $this->errorJson($validator->messages());
        
        } else {

            if ($app->save()) {
                //更新缓存
//                Cache::put($this->key.':'. $id, $app->find($id));
//                Cache::put($this->key.'_num', $id);

                return $this->successJson('添加成功');

            } else {

                return $this->errorJson('添加失败');
            }
        }
    }

    public function update()
    {

        $id = request()->id;

        $app = new UniacidApp();

        $info = $app->find($id);

        if (!$id || !$info) {
            return $this->errorJson('请选择应用');
        }

        if (request()->input()) {

            $data = $this->fillData(request()->input());
            $data['uniacid'] = $id;
            $data['id'] = $id;

            $app->fill($data);

            $validator = $app->validator($data);

            if ($validator->fails()) {

                return $this->errorJson($validator->messages());

            } else {

                if ($app->where('id', $id)->update($data)) {
                    //更新缓存
                    Cache::put($this->key . ':' . $id, $app->find($id), $data['validity_time']);

                    return $this->successJson('修改成功');
                } else {

                    return $this->errorJson('修改失败');
                }
            }
        }
    }

    //加入回收站 删除
    public function delete()
    {

        $id = request()->id;

        $info = UniacidApp::withTrashed()->find($id);

        if (!$id || !$info) {
            return $this->errorJson('请选择要修改的应用');
        }
        if ($info->deleted_at) {

            //强制删除
            if (!$info->forceDelete()) {
                return $this->errorJson('操作失败');
            }

            Cache::forget($this->key . ':' . $id);

        } else {

            if (!$info->delete()) {
                return $this->errorJson('操作失败');
            }

            Cache::put($this->key . ':' . $id, UniacidApp::find($id));
        }

        return $this->successJson('操作成功');
    }

    //启用禁用或恢复应用
    public function switchStatus()
    {

        $id = request()->id;

        $info = UniacidApp::withTrashed()->find($id);

        if (!$id || !$info) {
            return $this->errorJson('请选择要修改的应用');
        }

        if (request()->status) {
            //修改状态
            $res = UniacidApp::where('id', $id)->update(['status' => $info->status == 1 ? 0 : 1]);
        }

        if (request()->url) {
            //修改应用跳转链接
            $res = UniacidApp::where('id', $id)->update(['url' => filter_var(trim(request()->url), FILTER_VALIDATE_URL)]);
        }

        if ($info->deleted_at) {

            //从回收站中恢复应用
            $res = UniacidApp::withTrashed()->where('id', $id)->restore();
        }

        if ($res) {
            //更新缓存
            Cache::put($this->key . ':' . $id, UniacidApp::find($id), $info->validity_time);

            return $this->successJson('操作成功');
        } else {
            return $this->errorJson('操作失败');
        }
    }

    //回收站 列表
    public function recycle()
    {

        $list = UniacidApp::onlyTrashed()
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->toArray();

        if ($list) {
            return $this->successJson('获取成功', $list);
        } else {
            return $this->errorJson('获取失败,暂无数据');
        }
    }

    private function fillData($data)
    {
        return [
            'img' => $data['img'] ?  : 'http://www.baidu.com',
            'url' => $data['url'],
            'name' => $data['name'] ?  : 'test',
            'kind' => $data['kind'] ?  : '',
            'type' => $data['type'] ?  : 2,
            'title' => $data['title'] ?  : '',
            'descr' => $data['descr'] ?  : '',
            'status' => $data['status'] ?  : 1,
            // 'uniacid' => $app->insertGetId() + 1,
            'version' => $data['version'] ?  : 0.00,
            'validity_time' => $data['validity_time'] ?  : 0,
        ];
    }

    public function upload()
    {
        $file = request()->file('file');
        \Log::info('file', $file);

        if (!$file) {
            return $this->errorJson('请传入正确参数');
        }
        if ($file->isValid()) {
            $originalName = $file->getClientOriginalName(); // 文件原名
            \Log::info('originalName', $originalName);
            $realPath = $file->getRealPath();   //临时文件的绝对路径
            \Log::info('realPath', $realPath);

            $ext = $file->getClientOriginalExtension();
            \Log::info('ext', $ext);
            
            // $path = config('filesystems.disks.public')['root'].'/';   //后期存放路径

            $newOriginalName = date('Ymd').md5($originalName . str_random(6)) . '.' . $ext;
            \Log::info('newOriginalName', $newOriginalName);

            $res = \Storage::disk('public')->put($newOriginalName, file_get_contents($realPath));
            \Log::info('res-path', [$res, \Storage::disk('public')]);

            $proto = explode('/', $_SERVER['SERVER_PROTOCOL'])[0] === 'https' ? 'https://' : 'http://';

            return $this->successJson('上传成功', $proto.$_SERVER['HTTP_HOST'].'/storage/app/public/'.$newOriginalName);
        }
    }

    public function temp()
    {
        return View('admin.application.upload');
    }
}