<?php
/**
 * Created by PhpStorm.
 * User: *!N.j
 * Date: 2022/4/14
 * Time: 21:13
 */

namespace app\index\controller;

use library\Controller;
use think\facade\Request;
use think\Db;
use think\facade\Session;
use \app\index\model\Base as Base_m;
use \app\index\model\Api as Api_m;

class Api extends Controller
{
    public function every_day_user()
    {
        $now_time = date('Y-m-d');
        $list = Api_m::every_day_user($now_time);
        if ($list){
            foreach ($list as $k => $v){
                switch ($v['level'])
                {
                    case 0:$money = Base_m::get_config('l0_money');
                    break;
                    case 1:$money = Base_m::get_config('l1_money');
                    break;
                    case 2:$money = Base_m::get_config('l2_money');
                    break;
                    case 3:$money = Base_m::get_config('l3_money');
                    break;
                }
                Db::name('my_users')
                    ->where('id',$v['id'])
                    ->inc('money_1',$money)
                    ->data('every_day',$now_time)
                    ->update();
            }
            echo '已处理'.count($list).'条数据';
        }else{
            echo '没有数据';
        }
    }


    public function srsky_notify()
    {
        $key = '271690e0adb57d2a8220df3783e89a4b';
        if (Request::isPost()){
            $data = Request::post();
            wlog('data',$data);
            if ($data['respCode'] == '0000'){
                $sign = md5($data['orderId'].$data['respCode'].$key);
                wlog('sign',$sign);
                if ($sign == $data['sign']){
                    $res = Api_m::srsky_notify($data['orderId']);
                    wlog('res',$res);
                    switch ($res)
                    {
                        case 'success':
                        case 'error':
                            echo 'SUCCESS';
                            break;
                        case 'error_info':
                            wlog('fail',$data);
                            echo 'fail';
                            break;
                    }
                }else{
                    echo 'error_sign';
                    wlog('sign','no');
                }
            }
        }
    }

}