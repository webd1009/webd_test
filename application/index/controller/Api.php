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
    public function day_stock_f()
    {
        $now_time = date("Y-m-d");
        $where = [
            ['status', '=', 1],
            ['stock_f_time', '<', $now_time],
        ];
        $user_info = Db::name('xy_users')->where($where)->select();
        $stock_fenshu = Db::name('system_config')->where('name', 'miitbeian')->value('value');
        $stock_how_money = Db::name('system_config')->where('name', 'how_money')->value('value');

        if ($user_info) {
            foreach ($user_info as $k => $v) {
                switch ($v['is_buy']) {
                    case 0:
                        $shouyi = $v['stock_num'] / $stock_fenshu * $stock_how_money;
                        Db::name('xy_users')->where('id', $v['id'])->setInc('stock_money', $shouyi);
                        Db::name('xy_users')->where('id', $v['id'])->setField('stock_f_time', $now_time);
                        break;
                    case 1:
                        $shouyi = $v['stock_num'] / $stock_fenshu * $stock_how_money * 3;
                        Db::name('xy_users')->where('id', $v['id'])->setInc('stock_money', $shouyi);
                        Db::name('xy_users')->where('id', $v['id'])->setField('stock_f_time', $now_time);
                        break;
                }
            }
            wlog('shouyi', '已处理完成');
            echo '已处理完成';
        } else {
            wlog('shouyi', '没有要处理的数据');
            echo '没有要处理的数据';
        }
    }

    public function notify_url()
    {
        $data = $_POST;
        if ($data) {
            if ($data['pay_status'] == 1) {
                $pay_info = Db::name('xy_pay')->where('out_trade_no', $data['out_trade_no'])->find();
                if ($pay_info['status'] == 0) {
                    $user_recharge = Db::name('xy_users')->where('id', $pay_info['uid'])->setField('is_buy',1);
                    if ($user_recharge == true) {
                        $recharge_info['order_no'] = $pay_info['out_trade_no'];
                        $recharge_info['uid'] = $pay_info['uid'];
                        $recharge_info['tel'] = $pay_info['tel'];
                        $recharge_info['money'] = $pay_info['total_fee'] / 100;
                        $recharge_info['status'] = 1;
                        $recharge_info['addtime'] = time();
                        Db::name('xy_recharge')->insert($recharge_info);
                        $user_info = Db::name('xy_users')->where('id', $pay_info['uid'])->find();
                        self::pid_reward($user_info);
                        Db::name('xy_pay')->where('out_trade_no', $data['out_trade_no'])->setField('status',1);
                        echo 'success';
                    } else {
                        wlog('pay1', $pay_info);
                    }
                } else {
                    wlog('pay2', $pay_info);
                }
            } else {
                wlog('pay3', $data);
            }
        }
    }

    public function pid_reward($user_info)
    {
        if ($user_info['parent_id'] > 0) {
            $where['id'] = $user_info['parent_id'];
            $add_j_num = Db::name('xy_users')->where($where)->setInc('j_num', 1);
            $add_share_money = Db::name('xy_users')->where($where)->setInc('share_money', 100);
            if ($add_j_num == true && $add_share_money == true) {
                $j_num = Db::name('xy_users')->where($where)->value('j_num');
                switch ($j_num) {
                    case 1:
                        Db::name('xy_users')->where($where)->setInc('stock_num', 500);
                        break;
                    case 5:
                        Db::name('xy_users')->where($where)->setInc('stock_num', 700);
                        break;
                    case 10:
                        Db::name('xy_users')->where($where)->setInc('stock_num', 1000);
                        break;
                }
            } else {
                $info = '账号:' . $user_info['parent_id'] . '添加激活数失败';
                wlog('j_num', $info);
            }
        }
    }
}