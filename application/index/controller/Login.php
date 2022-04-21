<?php
/**
 * Created by PhpStorm.
 * User: *!N.j
 * Date: 2022/4/8
 * Time: 23:32
 */

namespace app\index\controller;

use library\Controller;
use think\facade\Request;
use think\facade\Session;
use think\facade\Config;
use \app\index\model\Login as Login_m;
use \app\index\model\Base as Base_m;

class Login extends Controller
{
    public function index()
    {
        if (Request::isPost()){
            $username = Request::post('username');
            if (!$username) return ['code'=>0,'msg'=>'手机号不能为空'];
            $password = Request::post('password');
            if (!$password) return ['code'=>0,'msg'=>'密码不能为空'];
            $check_name = Login_m::check_username($username);
            if ($check_name == 'success'){
                $check_login = Login_m::index($username,$password);
                if ($check_login != 'error'){
                    Session::set('uid',$check_login['id']);
                    return ['code'=>1,'msg'=>'登录成功'];
                }else{
                    return ['code'=>0,'msg'=>'账号或密码错误'];
                }
            }else{
                return ['code'=>0,'msg'=>'账号或密码错误'];
            }
        }
        return $this->fetch();
    }

    public function reg()
    {
        $invite_code = Request::route();
        if (!empty($invite_code)){
            $invite_code = $invite_code[1];
            $this->assign('invite_code',$invite_code);
        }
        if (Request::isPost()){
            $username = Request::post('username');
            if (!$username) return ['code'=>0,'msg'=>'手机号不能为空'];
            $check_tel = Login_m::check_tel($username);
            if ($check_tel['code'] == 0) return $check_tel;
            $password = Request::post('password');
            if (!$password) return ['code'=>0,'msg'=>'密码不能为空'];
            $invite_code = Request::post('invite_code');
            $check_name = Login_m::check_username($username);
            if ($check_name == 'error'){
                $info['username'] = $username;
                $info['password'] = md5($password);
                if ($invite_code){
                    $info['pid'] = Login_m::get_pid_info($invite_code);
                }else{
                    $info['pid'] = 0;
                }
                $info['invite_code'] = \app\index\model\Code::randString($len = 8 , $type = 5);
                $info['money_1'] = Base_m::get_config('reg_money');
                $info['every_day'] = date('Y-m-d');
                $info['create_time'] = time();
                $res = Login_m::reg($info);
                if ($res['code'] == 1){
                    Session::set('uid',$res['data']);
                    return ['code'=>1,'msg'=>'注册成功'];
                }else{
                    return ['code'=>0,'msg'=>'注册失败,请联系客服!'];
                }
            }else{
                return ['code'=>0,'msg'=>'该手机号已被注册'];
            }
        }
        return $this->fetch();
    }

}