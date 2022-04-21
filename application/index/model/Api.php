<?php
/**
 * Created by PhpStorm.
 * User: *!N.j
 * Date: 2022/4/14
 * Time: 21:13
 */

namespace app\index\model;

use think\facade\Request;
use think\Model;
use think\Db;
use think\facade\Config;
use \app\index\model\Base as Base_m;

class Api extends Model
{
    public static function every_day_user($now_time)
    {
        $where[] = ['every_day','<',$now_time];
        $where[] = ['status','=',1];
        return Db::name('my_users')->where($where)->limit(5000)->select();
    }

    public static function srsky_notify($order_id)
    {
        $order_info = Db::name('my_join')->where('order_id',$order_id)->find();
        if ($order_info['status'] == 1){
            $update['is_join'] = 1;
            $update['update_time'] = time();
            $res = Db::name('my_users')->where('id',$order_info['uid'])->update($update);
            if ($res == true){
                Db::name('my_join')->where('order_id',$order_id)->setField('status',2);
                return 'success';
            }else{
                return 'error';
            }
        }else{
            return 'error_info';
        }
    }


}