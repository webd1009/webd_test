<?php

namespace app\index\controller;

use library\command\Sess;
use think\facade\Request;
use think\facade\Session;
use think\facade\Config;
use \app\index\model\Base as Base_m;

/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Index extends Base
{
    public function index()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        $this->assign('user_info',$user_info);
        $notice = Base_m::get_config('notice');
        $this->assign('notice',$notice);

        $reg_money = Base_m::get_config('reg_money');
        $this->assign('reg_money',$reg_money);

        $pid_reg_money = Base_m::get_config('pid_reg_money');
        $this->assign('pid_reg_money',$pid_reg_money);

        $l0_money = Base_m::get_config('l0_money');
        $this->assign('l0_money',$l0_money);

        $this->create_qr_code();
        return $this->fetch();
    }

}
