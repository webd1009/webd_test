<?php

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;
use \app\index\model\Base as Base_m;
use think\facade\Request;

class Pay extends Model
{
    private static $url = 'http://api.api.cn/php/createOrder.do';
    private static $branchCode = '111';
    private static $merchId = '222';
    private static $transType = '333';
    private static $key = '444';

    public static function pay_money_2_do($user_info)
    {
        $orderId = time().mt_rand(11111,99999);
        $pay_money2 = Base_m::get_config('pay_money2');
        $amount = $pay_money2 * 100;
        $notifyUrl = Request::domain().'/index/api/pay_do';
        $frontUrl = Request::domain().'/index/pay/index';
        $sign = md5($orderId.$amount.self::$merchId.self::$key);

        $data['orderId'] = $orderId;
        $data['amount'] = $amount;
        $data['notifyUrl'] = $notifyUrl;
        $data['frontUrl'] = $frontUrl;
        $data['branchCode'] = self::$branchCode;
        $data['merchId'] = self::$merchId;
        $data['transType'] = self::$transType;
        $data['fromIp'] = get_client_ip();
        $data['sign'] = $sign;
        $res = self::send_post(self::$url,$data);
        if ($res['code'] == '0100'){
            $order_info['order_id'] = $orderId;
            $order_info['uid'] = $user_info['id'];
            $order_info['username'] = $user_info['username'];
            $order_info['bank_name'] = $user_info['bank_name'];
            $order_info['bank_card'] = $user_info['bank_card'];
            $order_info['real_name'] = $user_info['real_name'];
            $order_info['card_id'] = $user_info['card_id'];
            $order_info['money'] = $pay_money2;
            $order_info['type'] = 2;
            $order_info['status'] = 1;
            $order_info['create_time'] = time();
            $add = Db::name('my_pay')->insert($order_info);
            if ($add == true){
                return ['code'=>1,'data'=>$res['data']['html']];
            }else{
                return ['code'=>0,'msg'=>'创建失败,请稍后尝试!'];
            }
        }else{
            return ['code'=>0,'msg'=>'创建失败,请稍后尝试!'];
        }
    }

    public static function pay_money_2($user_info)
    {
        $money_2 = Base_m::get_config('money_2');
        $data['money_2_time'] = date('Y-m-d');
        $data['is_pay'] = 1;
        $money = Base_m::get_config('pay_money2');
        $res = Db::name('my_users')->where('id',$user_info['id'])->inc('money_2',$money_2)->update($data);
        if ($res == true){
            if ($user_info['pid'] > 0){
                self::pid_money($user_info,$money);
            }
            self::add_money_t($user_info['id'],$field = 'money_2_t',$money_2);
            self::add_log($user_info,$money,$type = 3);
            self::m_day_1($user_info['id'],$type = 3,$money_2);
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function pay_money_3_do($user_info)
    {
        $orderId = time().mt_rand(11111,99999);
        $pay_money3 = Base_m::get_config('pay_money3');
        $amount = $pay_money3 * 100;
        $notifyUrl = Request::domain().'/index/api/pay_do';
        $frontUrl = Request::domain().'/index/pay/index';
        $sign = md5($orderId.$amount.self::$merchId.self::$key);

        $data['orderId'] = $orderId;
        $data['amount'] = $amount;
        $data['notifyUrl'] = $notifyUrl;
        $data['frontUrl'] = $frontUrl;
        $data['branchCode'] = self::$branchCode;
        $data['merchId'] = self::$merchId;
        $data['transType'] = self::$transType;
        $data['fromIp'] = get_client_ip();
        $data['sign'] = $sign;
        $res = self::send_post(self::$url,$data);
        if ($res['code'] == '0100'){
            $order_info['order_id'] = $orderId;
            $order_info['uid'] = $user_info['id'];
            $order_info['username'] = $user_info['username'];
            $order_info['bank_name'] = $user_info['bank_name'];
            $order_info['bank_card'] = $user_info['bank_card'];
            $order_info['real_name'] = $user_info['real_name'];
            $order_info['card_id'] = $user_info['card_id'];
            $order_info['money'] = $pay_money3;
            $order_info['type'] = 3;
            $order_info['status'] = 1;
            $order_info['create_time'] = time();
            $add = Db::name('my_pay')->insert($order_info);
            if ($add == true){
                return ['code'=>1,'data'=>$res['data']['html']];
            }else{
                return ['code'=>0,'msg'=>'创建失败,请稍后尝试!'];
            }
        }else{
            return ['code'=>0,'msg'=>'创建失败,请稍后尝试!'];
        }
    }



    public static function pay_money_3($user_info)
    {
        $money = Base_m::get_config('pay_money3');
        $data['money_3_time'] = date('Y-m-d');
        $res = Db::name('my_users')->where('id',$user_info['id'])->inc('money_3_num',$money)->update($data);
        if ($res == true){
            if ($user_info['pid'] > 0){
                self::pid_money($user_info,$money);
            }
            self::add_log($user_info,$money,$type = 3);
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function pid_money($user_info,$money)
    {
        $pay_ratio = Base_m::get_config('pay_ratio');
        $pid_money = $money * $pay_ratio / 100;
        Db::name('my_users')->where('id',$user_info['pid'])->setInc('money_1',$pid_money);
        Db::name('my_users')->where('id',$user_info['id'])->setInc('pid_money',$pid_money);
    }

    public static function m_day_1($uid,$type,$money)
    {
        $now_time = date('Y-m-d');
        $where['uid'] = $uid;
        $where['type'] = $type;
        $where['time'] = $now_time;
        $money_day = Db::name('my_m_day_1')->where($where)->find();
        if ($money_day){
            Db::name('my_m_day_1')->where('id',$money_day['id'])->setInc('money',$money);
        }else{
            $data['uid'] = $uid;
            $data['type'] = $type;
            $data['money'] = $money;
            $data['time'] = $now_time;
            Db::name('my_m_day_1')->insert($data);
        }
    }

    public static function add_money_t($uid,$field,$money)
    {
        Db::name('my_users')->where('id',$uid)->setInc($field,$money);
    }

    public static function add_log($user_info,$money,$type)
    {
        $data['order_id'] = time().mt_rand(11111,99999);
        $data['uid'] = $user_info['id'];
        $data['username'] = $user_info['username'];
        $data['bank_name'] = $user_info['bank_name'];
        $data['bank_card'] = $user_info['bank_card'];
        $data['real_name'] = $user_info['real_name'];
        $data['card_id'] = $user_info['card_id'];
        $data['money'] = $money;
        $data['type'] = $type;
        $data['status'] = 2;
        $data['create_time'] = time();
        Db::name('my_pay')->insert($data);
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
        return json_decode($output,true);
    }



}
