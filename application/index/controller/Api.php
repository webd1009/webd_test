<?php

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
    
    public function pay_do()
    {
        if (Request::isPost()){
            $data = Request::post();
            if ($data['respCode'] == '0000'){
                $order_info = Db::name('my_pay')->where('order_id',$data['orderId'])->where('status',1)->find();
                if ($order_info){
                    $user_info = Base_m::get_user_info($order_info['uid']);
                    $res = Api_m::pay_do($user_info,$order_info);
                    switch ($res)
                    {
                        case 'success':
                            echo 'SUCCESS';
                            break;
                        case 'error':
                            wlog('data',$data);
                            echo 'fail';
                            break;
                    }
                }
            }
        }
    }

}
