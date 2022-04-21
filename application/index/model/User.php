<?php
/**
 * Created by PhpStorm.
 * User: *!N.j
 * Date: 2022/4/21
 * Time: 20:12
 */

namespace app\index\model;

use think\Model;
use think\Db;
use think\facade\Config;
use \app\index\model\Base as Base_m;

class User extends Model
{
    public static function check_real_name($real_name)
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]{2,10}$/u', $real_name);
    }

    public static function check_card_id($card_id)
    {
        $City = array(
            '11', '12', '13', '14', '15', '21', '22',
            '23', '31', '32', '33', '34', '35', '36',
            '37', '41', '42', '43', '44', '45', '46',
            '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91'
        );

        // 身份证不是17+xX或18位数字或15位数字
        if (!preg_match('/^([\d]{14,18}[xX\d]|[\d]{15})$/', $card_id)) {
            return false;
        }
        // 身份证城市不在列表中
        if (!in_array(substr($card_id, 0, 2), $City)) {
            return false;
        }
        $card_id = preg_replace('/[xX]$/i', 'a', $card_id);
        $length = strlen($card_id);
        if ($length == 18) {
            $vBirthday = substr($card_id, 6, 4) . '-' . substr($card_id, 10, 2) . '-' . substr($card_id, 12, 2);
        } else {
            $vBirthday = '19' . substr($card_id, 6, 2) . '-' . substr($card_id, 8, 2) . '-' . substr($card_id, 10, 2);
        }
        // 生日验证,并且如果生日大于现在的时间，也报错
        $birthdayTime = strtotime($vBirthday);
        if (date('Y-m-d', $birthdayTime) != $vBirthday && $birthdayTime > time()) {
            return false;
        }
        return true;
    }

    public static function account($uid,$info)
    {
        if ($info['real_name'] && $info['card_id']){
            $info['is_auth'] = 1;
        }
        $res = Db::name('my_users')->where('id',$uid)->update($info);
        if ($res == true){
            $user_info = Base_m::get_user_info($uid);
            if ($user_info['is_auth'] == 1 && $user_info['pid'] > 0){
                self::pid_money($user_info['pid']);
            }
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function pid_money($pid)
    {
        $pid_reg_money = Base_m::get_config('pid_reg_money');
        if ($pid > 0){
            Db::name('my_users')->where('id',$pid)->setInc('money_2',$pid_reg_money);
        }
    }

    public static function team_data($uid)
    {
        $data['num'] = Db::name('my_users')->where('pid', $uid)->count();
        $data['join'] = Db::name('my_users')->where('pid', $uid)->where('is_pay', 1)->count();
        return $data;
    }

    public static function team_info($uid, $page)
    {
        $data['data'] = Db::name('my_users')
            ->where('pid', $uid)
            ->order('id', 'desc')
            ->field('id,username,head,is_pay,level,create_time')
            ->page($page, 10)->select();
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['username'] = substr_replace($value['username'], '****', 3, 4);
            $data['data'][$key]['create_time'] = toDate($value['create_time']);
        }
        return $data;
    }

    public static function server()
    {
        return Db::name('system_cs')->where('status',1)->paginate(10,false,['query'=>request()->param()]);
    }

    public static function password($uid, $r_pass)
    {
        $data['update_time'] = time();
        $data['password'] = md5($r_pass);
        $res = Db::name('my_users')->where('id', $uid)->update($data);
        if ($res == true) {
            return 'success';
        } else {
            return 'error';
        }
    }

    public static function cashed($user_info,$money_type,$money,$type)
    {
        $dec = Base_m::dec_money($user_info['id'], $money_type, $money);
        if ($dec == 'success') {
            $add_cashed = self::add_cashed($user_info, $money, $type);
            if ($add_cashed == 'success') {
                return 'success';
            } else {
                Base_m::inc_money($user_info['id'], $money_type, $money);
                return 'error';
            }
        }
    }

    public static function add_cashed($user_info, $money, $type)
    {
        $data['uid'] = $user_info['id'];
        $data['pid'] = $user_info['pid'];
        $data['real_name'] = $user_info['real_name'];
        $data['card_id'] = $user_info['card_id'];
        $data['username'] = $user_info['username'];
        $data['bank_name'] = $user_info['bank_name'];
        $data['bank_card'] = $user_info['bank_card'];
        $data['type'] = $type;
        $data['money'] = $money;
        $data['status'] = 1;
        $data['create_time'] = time();
        $add = Db::name('my_cashed')->insert($data);
        if ($add == true) {
            return 'success';
        } else {
            return 'error';
        }
    }

}