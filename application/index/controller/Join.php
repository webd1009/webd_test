<?php
/**
 * Created by PhpStorm.
 * User: *!N.j
 * Date: 2022/4/10
 * Time: 3:37
 */

namespace app\index\controller;

use think\facade\Request;
use think\facade\Session;
use think\facade\Config;
use \app\index\model\Base as Base_m;
use \app\index\model\Join as Join_m;

class Join extends Base
{
    public function index()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        $this->assign('user_info',$user_info);

        $l1_pay = Base_m::get_config('l1_pay');
        $this->assign('l1_pay',$l1_pay);

        $l2_pay = Base_m::get_config('l2_pay');
        $this->assign('l2_pay',$l2_pay);

        $l3_pay = Base_m::get_config('l3_pay');
        $this->assign('l3_pay',$l3_pay);

        return $this->fetch();
    }

    public function join_pay()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        if (Request::isPost()){
            $pay_switch = Base_m::get_config('pay_switch');
            if ($pay_switch == 0) return ['code'=>0,'msg'=>'抱歉,加入通道正在维护,请稍后再试.'];
            $type = Request::post('type');
            if ($user_info['level'] == $type) return ['code'=>0,'msg'=>'您已经是该志愿者了'];
            if ($type < $user_info['level']) return ['code'=>0,'msg'=>'抱歉,您已经是高级别志愿者了'];
            $res =  Join_m::join_pay($user_info,$type);
            switch ($res)
            {
                case 'success':
                    return ['code'=>1,'msg'=>'恭喜,加入志愿者成功'];
                    break;
                case 'error':
                    return ['code'=>0,'msg'=>'抱歉,加入失败,请联系客服'];
                    break;
            }
        }
    }

}