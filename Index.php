<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;
use SmsSDK\REST;

class Index extends Controller
{
    public function home()
    {
        $uid = Session::get('user_id');
        if ($uid == false) {
            $this->error('请先登录', Url('index/index'));
        } else {
            $user_info = \app\index\model\Index::get_user_info($uid);
            $this->assign('user_info', $user_info);
            return $this->fetch();
        }
    }

    public function index()       //登录验证
    {
        $is_login = Session::has('user_id');
        if ($is_login != false) {
            $this->success('您已登录', Url('index/home'));
        }
        if (Request::isPost()) {
            $username = trim(Request::post('username'));
            if (!$username) return ['code' => 0, 'msg' => '手机号不能为空'];
            $password = trim(Request::post('password'));
            if (!$password) return ['code' => 0, 'msg' => '密码不能为空'];
            $check_info = \app\index\model\Index::checkuser($username, $password);
            if ($check_info !== false) {
                if ($check_info['status'] == 0) $this->error('您的账号已被封禁');
                Session::set('user_id', $check_info['id']);
                $data['last_login_time'] = time();
                $data['last_login_ip'] = get_client_ip();
                $data['total_login_num'] = $check_info['total_login_num'] + 1;
                $res = Db::name('user')->where('id', $check_info['id'])->update($data);
                if ($res == true) {
                    return ['code' => 1, 'msg' => '登录成功'];
                }
            } else {
                return ['code' => 0, 'msg' => '账号或密码错误'];
            }
        }
        return $this->fetch();
    }

    public function logout()  //退出登录
    {
        Session::delete('user_id');
        return ['code' => 1, 'msg' => '已安全退出'];
    }

    public function register()
    {
        $pid_code = Request::route('pid');
        $pid = \app\index\model\Index::pid_info($pid_code);
        $this->assign('pid', $pid);
        if (Request::isPost()) {
            $tel = trim(Request::post('tel'));
            $pid = trim(Request::post('pid'));
            $n_pass = trim(Request::post('n_pass'));
            $r_pass = trim(Request::post('r_pass'));
            $check_tel = \app\index\model\Index::check_tel($tel);
            if ($check_tel == 'error') {
                return ['code' => 0, 'msg' => '该账号已存在'];
            }
            if ($n_pass != $r_pass) {
                return ['code' => 0, 'msg' => '两次密码不一致'];
            }
            if ($pid == null) {
                $pid = 0;
            }
            $add_user = \app\index\model\Index::register($tel, $pid, $r_pass);
            if ($add_user == 'success') {
                return ['code' => 1, 'msg' => '注册赠送的黄金跟数字货币已到账'];
            } else {
                return ['code' => 0, 'msg' => '系统错误,请联系客服!'];
            }
        }

        return $this->fetch();
    }

    public function forget()
    {
        if (Request::isPost()) {
            $tel = trim(Request::post('tel'));
            $code = trim(Request::post('code'));
            $n_pass = trim(Request::post('n_pass'));
            $r_pass = trim(Request::post('r_pass'));
            if ($n_pass != $r_pass) {
                return ['code' => 0, 'msg' => '两次密码不一致'];
            }
            $check_code = \app\index\model\Index::check_code($tel, $code, 2);
            if ($check_code != 'error') {
                $forget = \app\index\model\Index::forget($tel, $check_code, $r_pass);
                if ($forget == 'success') {
                    return ['code' => 1, 'msg' => '修改成功'];
                } else {
                    return ['code' => 0, 'msg' => '修改失败,请联系客服!'];
                }
            } else {
                return ['code' => 0, 'msg' => '验证码错误'];
            }
        }
        return $this->fetch();
    }

    public function reg_sms()
    {
        if (Request::isPost()) {
            $tel = trim(Request::post('tel'));
            $check_tel = \app\index\model\Index::check_tel($tel);
            if ($check_tel == 'success') {
                $send_sms = \app\index\model\Index::send_sms($tel, 1);
                if ($send_sms == 'success') {
                    return ['code' => 1, 'msg' => '发送成功,请查看手机短信'];
                } else {
                    return ['code' => 0, 'msg' => '发送失败,请联系客服!'];
                }
            } else {
                return ['code' => 0, 'msg' => '该账号已存在'];
            }
        }
    }

    public function forget_sms()
    {
        if (Request::isPost()) {
            $tel = trim(Request::post('tel'));
            $check_tel = \app\index\model\Index::check_tel($tel);
            if ($check_tel == 'error') {
                $send_sms = \app\index\model\Index::send_sms($tel, 2);
                if ($send_sms == 'success') {
                    return ['code' => 1, 'msg' => '发送成功,请查看手机短信'];
                } else {
                    return ['code' => 0, 'msg' => '发送失败,请联系客服!'];
                }
            } else {
                return ['code' => 0, 'msg' => '该账号不存在'];
            }
        }
    }

    public function notice_info()
    {
        if (Request::isPost()) {
            $notice_info = \app\index\model\Index::notice_info();
            if ($notice_info) {
                foreach ($notice_info as $k => $v) {
                    $info[] = $v['title'];
                }
                return ['code' => 1, 'data' => $info];
            } else {
                return ['code' => 0, 'msg' => '获取失败'];
            }
        }
    }


}
