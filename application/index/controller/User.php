<?php
/**
 * Created by PhpStorm.
 * User: *!N.j
 * Date: 2022/4/21
 * Time: 19:55
 */

namespace app\index\controller;

use think\facade\Request;
use think\facade\Session;
use think\facade\Config;
use \app\index\model\Base as Base_m;
use \app\index\model\User as User_m;

class User extends Base
{
    public function index()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        $this->assign('user_info',$user_info);
        return $this->fetch();
    }

    public function logout()
    {
        if (Request::isPost()) {
            Session::delete('uid');
            return ['code' => 1, 'msg' => '已安全退出'];
        }
    }

    public function account()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        $this->assign('user_info',$user_info);
        if (Request::isPost()){
            $uid = Request::post('uid');

            $info['real_name'] = Request::post('real_name');
            $check_real_name = User_m::check_real_name($info['real_name']);
            if ($check_real_name == false) return ['code'=>0,'msg'=>'真实姓名格式错误'];

            $info['card_id'] = Request::post('card_id');
            $check_card_id = User_m::check_card_id($info['card_id']);
            if ($check_card_id == false) return ['code'=>0,'msg'=>'身份证号格式错误'];

            $info['bank_name'] = Request::post('bank_name');
            $info['bank_card'] = Request::post('bank_card');
            $info['update_time'] = time();
            $res = User_m::account($uid,$info);
            switch ($res)
            {
                case 'success':
                    return ['code'=>1,'msg'=>'保存成功'];
                    break;
                case 'error':
                    return ['code'=>0,'msg'=>'保存失败,系统错误,请联系客服!'];
                    break;
            }
        }
        return $this->fetch();
    }

    public function team()
    {
        $uid = Session::get('uid');
        $data = User_m::team_data($uid);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function team_info()
    {
        $uid = Session::get('uid');
        $page = Request::post('page');
        return User_m::team_info($uid, $page);
    }

    public function poster()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        $this->create_qr_code();
        $http = Request::domain();
        $qr = $http.'/qrcode/user/'.$uid.'/'.$uid.'_01.png';
        $this->assign('qr',$qr);
        $url = $http.'/reg/invite_code/'.$user_info['invite_code'];
        $this->assign('url',$url);
        return $this->fetch();
    }

    public function server()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        $this->assign('user_info',$user_info);
        $list = User_m::server();
        $page = $list->render();  //构造分页
        $this->assign('pages', $page);   //输出分页
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function password()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        if (Request::isPost()) {
            $o_pass = trim(Request::post('o_pass'));
            $n_pass = trim(Request::post('n_pass'));
            $r_pass = trim(Request::post('r_pass'));
            if ($n_pass != $r_pass) return ['code' => 0, 'msg' => '两次密码不一致'];
            if (md5($o_pass) != $user_info['password']) return ['code' => 0, 'msg' => '原始密码错误'];
            $res = User_m::password($uid, $r_pass);
            switch ($res) {
                case 'success':
                    return ['code' => 1, 'msg' => '修改成功'];
                    break;
                case 'error':
                    return ['code' => 0, 'msg' => '修改失败,请联系客服!'];
                    break;
            }
        }
        return $this->fetch();
    }

    public function cashed()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        if (Request::isPost()){
            $type = Request::post('type');
            switch ($type)
            {
                case 1:$money_type = 'money_1';
                break;
                case 2:$money_type = 'money_2';
                break;
                case 3:$money_type = 'money_3';
                break;
            }
            $money = Request::post('money');
            if ($money == '') return ['code'=>0,'msg'=>'金额不正确,请重试.'];
            if (!$money || $money <= 0) return ['code'=>0,'msg'=>'金额不正确,请重试.'];
            if (!is_numeric($money)) return ['code'=>0,'msg'=>'金额不正确,请重试.'];
            if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/',$money)) return ['code'=>0,'msg'=>'金额不正确,请重试.'];
            $check_money =  $user_info[$money_type] - $money;
            if ($check_money < 0) return ['code'=>0,'msg'=>'可提现金额不足'];;
            if ($money > $user_info[$money_type]) return ['code'=>0,'msg'=>'可提现金额不足'];

            $res = User_m::cashed($user_info,$money_type,$money,$type);
            switch ($res)
            {
                case 'success':
                    return ['code'=>1,'msg'=>'申请提现成功,请等待后台审核!'];
                    break;
                case 'error':
                    return ['code'=>0,'msg'=>'申请提现失败,请联系客服!'];
                    break;
            }
        }
    }

}