<?php
/**
 * Created by PhpStorm.
 * User: *!N.j
 * Date: 2022/4/10
 * Time: 3:38
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;
use \app\index\model\Base as Base_m;
use think\facade\Request;

class Join extends Model
{
    public static function join_pay($user_info,$type)
    {
        switch ($type)
        {
            case 1:$money = Base_m::get_config('pay1_money');
            break;
            case 2:$money = Base_m::get_config('pay2_money');
            break;
            case 3:$money = Base_m::get_config('pay3_money');
            break;
        }
        $res = Db::name('my_users')->where('id',$user_info['id'])
            ->inc('money_3',$money)
            ->data('is_pay',1)
            ->data('level',$type)
            ->data('update_time',time())
            ->update();
        if ($res == true){
            self::add_join_list($user_info,$orderId = mt_rand(123456,654321),$type);
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function add_join_list($user_info,$orderId,$type)
    {
        switch ($type)
        {
            case 1:$money = Base_m::get_config('l1_pay');
                break;
            case 2:$money = Base_m::get_config('l2_pay');
                break;
            case 3:$money = Base_m::get_config('l3_pay');
                break;
        }
        $data['order_id'] = $orderId;
        $data['uid'] = $user_info['id'];
        $data['pid'] = $user_info['pid'];
        $data['username'] = $user_info['username'];
        $data['money'] = $money;
        $data['status'] = 2;
        $data['create_time'] = time();
        $add = Db::name('my_join')->insert($data);
        if ($add == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function send_post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        $rst = json_decode($output,true);
        return $rst;
    }

}
