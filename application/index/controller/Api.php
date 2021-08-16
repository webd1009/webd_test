<?php

namespace app\index\controller;

use library\Controller;
use think\facade\Request;
use think\facade\Session;
use think\Model;
use think\Db;
use think\facade\Config;


/**
 * 控制器
 */
class Api extends Controller
{
    public function money_s_time()
    {
        $now_time = date("Y-m-d");
        $where = [
            ['money_b', '>', 0],
            ['status', '=', 1],
            ['money_s_time', '<', $now_time],
        ];
        $user_info = Db::name('xy_users')->where($where)->select();
        $day_money = Db::name('system_config')->where('name', 'miitbeian')->value('value');
        if ($user_info) {
            foreach ($user_info as $k => $v) {
                $money_s = $v['money_b'] * 0.1 + $day_money;
                Db::name('xy_users')->where('id', $v['id'])->setInc('money_s', $money_s);
                Db::name('xy_users')->where('id', $v['id'])->setField('money_s_time', $now_time);
            }
            wlog('money_s', '已处理完成');
            echo '已处理完成';
        } else {
            wlog('money_s', '没有要处理的数据');
            echo '没有要处理的数据';
        }
    }

    public function notify_url()
    {
        $data = $_POST;
        if ($data) {
            if ($data['pay_status'] == 1) {
                $pay_info = Db::name('xy_insure_log')->where('out_trade_no', $data['out_trade_no'])->find();
                if ($pay_info['status'] == 0) {
                    $user_info = Db::name('xy_users')->where('id', $pay_info['uid'])->find();
                    $update['money_q'] = 0;
                    $pay = Db::name('xy_users')
                        ->where('id',$user_info['id'])
                        ->Inc('money_b',$pay_info['money'])
                        ->inc('money_s',$pay_info['money'])
                        ->update($update);
                    if ($pay == true) {
                        switch ($pay_info['money']) {
                            case 499:
                                Db::name('xy_users')->where('id', $user_info['id'])->setField('is_buy_l', 1);
                                break;
                            case 699:
                                Db::name('xy_users')->where('id', $user_info['id'])->setField('is_buy_h', 1);
                                break;
                        }
                        self::pid_money($user_info);
                        Db::name('xy_insure_log')->where('out_trade_no', $data['out_trade_no'])->setField('status', 1);
                        echo 'success';
                    } else {
                        wlog('pay1', $pay_info);
                    }
                } else {
                    wlog('pay2', $data);
                }
            }
        }
    }

    //返利
    public function pid_money($user_info)
    {
        if ($user_info['pid'] > 0 && $user_info['pid_money'] == 0) {
            Db::name('xy_users')->where('id', $user_info['pid'])->setInc('money_q', 100);
            Db::name('xy_users')->where('id', $user_info['pid'])->setInc('pid_num', 1);
            Db::name('xy_users')->where('id', $user_info['id'])->setField('pid_money', 1);
        } else {
            return false;
        }
    }

}
