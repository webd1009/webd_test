<?php
/**
 * Created by PhpStorm.
 * User: *!N.j
 * Date: 2022/4/8
 * Time: 23:33
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;

class Login extends Model
{
    public static function check_tel($username)
    {
        if (!is_numeric($username)) {
            return ['code'=>0,'msg'=>'请正确输入手机号'];
        }
        if(!preg_match("/^1[23456789]{1}\d{9}$/",$username)){
            return ['code'=>0,'msg'=>'您输入的手机号格式不正确'];
        }
        return ['code'=>1,'data'=>$username];
    }

    public static function check_username($username)
    {
        $check_name = Db::name('my_users')->where('username',$username)->count();
        if ($check_name > 0){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function get_pid_info($invite_code)
    {
        $pid_info = Db::name('my_users')->where('invite_code',$invite_code)->find();
        if ($pid_info){
            return $pid_info['id'];
        }else{
            return 0;
        }
    }

    public static function reg($info)
    {
        $res = Db::name('my_users')->insertGetId($info);
        if ($res == true){
            return ['code'=>1,'data'=>$res];
        }else{
            return ['code'=>0];
        }
    }

    public static function index($username,$password)
    {
        $user_info = Db::name('my_users')->where('username',$username)->find();
        if ($user_info){
            if ($user_info['password'] == md5($password)){
                return $user_info;
            }else{
                return 'error';
            }
        }else{
            return 'error';
        }
    }


}