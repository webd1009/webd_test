<?php
/**
 * Created by PhpStorm.
 * User: *!N.J
 * Date: 2021/6/7
 * Time: 21:29
 * QQ:1467572213
 */

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Config;
use think\facade\Session;
use \app\index\model\Upload as Upload_m;

class Upload extends controller
{
    public function upload()
    {
        $uid = Session::get('uid');
        $file = request()->file('file');
        if ($file->checkExt('php,sh,asp,asa,cer,aspx,php0,php1,php2,php3,php4,php5')) {
            return ['code'=>0,'msg'=>'可执行文件禁止上传到本地服务器'];
        }else{
            $info = $file->move('upload');
            $filename = $info->getSaveName();
            if ($filename){
                $add = Upload_m::add_head_img($uid,$filename);
                switch ($add)
                {
                    case 'success':
                        return ['code'=>1,'msg'=>'上传成功','data'=>$filename];
                        break;
                    case 'error':
                        return ['code'=>0,'msg'=>'上传失败'];
                }
            }
        }
    }
}