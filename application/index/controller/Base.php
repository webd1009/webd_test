<?php
namespace app\index\controller;

use library\Controller;
use think\facade\Request;
use think\Db;
use think\facade\Session;
use \app\index\model\Base as Base_m;

/**
 * 验证登录控制器
 */
class Base extends Controller
{
    public function initialize()
    {
        $is_login = Session::has('uid');
        if ($is_login == false) {
            $this->redirect('/login');
        }
    }

    public function create_qr_code()
    {
        $uid = Session::get('uid');
        $user_info = Base_m::get_user_info($uid);
        $qr_config_01['img_name'] = '_01';
        $qr_config_01['hb_img'] = 'hb02.jpg';
        $qr_config_01['size'] = 160;
        $qr_config_01['width'] = 180;
        $qr_config_01['height'] = 620;
        Base_m::create_qr_code($user_info['id'],$user_info['invite_code'],$qr_config_01,$be = false);
    }

}
