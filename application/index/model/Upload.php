<?php
/**
 * Created by PhpStorm.
 * User: Knight
 * Date: 2021/10/27
 * Time: 10:32
 * QQ:1467572213
 */

namespace app\index\model;

use think\Db;
use think\facade\Config;
use think\Model;

class Upload extends Model
{
    public static function add_head_img($uid,$filename)
    {
        $add = Db::name('my_users')->where('id',$uid)->setField('head',$filename);
        if ($add == true){
            return 'success';
        }else{
            return 'error';
        }
    }

}