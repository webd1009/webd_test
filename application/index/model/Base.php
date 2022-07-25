<?php
/**
 * Created by PhpStorm.
 * User: *!N.j
 * Date: 2022/4/8
 * Time: 23:33
 */

namespace app\index\model;

use think\facade\Request;
use think\Model;
use think\Db;
use think\facade\Config;

class Base extends Model
{
    public static function get_user_info($uid)
    {
        return Db::name('my_users')->where('id',$uid)->find();
    }

    public static function get_config($key)
    {
        return Db::name('my_config')->where('key',$key)->value('value');
    }

    public static function inc_money($uid,$field,$money)
    {
        $res = Db::name('my_users')->where('id',$uid)->setInc($field,$money);
        if ($res == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    public static function dec_money($uid,$field,$money)
    {
        $res = Db::name('my_users')->where('id',$uid)->setDec($field,$money);
        if ($res == true){
            return 'success';
        }else{
            return 'error';
        }
    }

    //生成用户二维码
    public static function create_qr_code($uid,$invite_code,$qr_config,$be)
    {
        if ($be == true){
            $qr_img = './qrcode/user/'.$uid.'/'.$uid.$qr_config['img_name'].'.png';
            if(file_exists($qr_img)) {
                return;
            }
        }
        $http = Request::domain();
        $qrCode = new \Endroid\QrCode\QrCode($http . url('@reg/invite_code/'.$invite_code));
        //设置前景色
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' =>0, 'a' => 0]);
        //设置背景色
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        //设置二维码大小
        $qrCode->setSize($qr_config['size']);
        $qrCode->setPadding(5);
        $qrCode->setLogoSize(40);
        $qrCode->setLabelFontSize(14);
        $qrCode->setLabelHalign(100);

        $folder = './qrcode/user/'.$uid;
        if(!file_exists($folder)) {
            mkdir($folder, 0777,true);
        }
        $qrCode->save($folder . '/' . $uid . '.png');

        $qr = \Env::get('root_path').'public/qrcode/user/' . $uid . '/' . $uid . '.png';
        $hb = \Env::get('root_path').'public/qrcode/'.$qr_config['hb_img'];

        $image = \think\Image::open($hb);
        $image->water($qr,[$qr_config['width'],$qr_config['height']])->save(\Env::get('root_path').'public/qrcode/user/'.$uid.'/'.$uid.$qr_config['img_name'].'.png');
    }
    
    public static function create_join($uid,$real_name,$time,$qr_config,$be)
    {
        $hb = \Env::get('root_path').'public/qrcode/'.$qr_config['hb_img'];
        $image = \think\Image::open($hb);
        $image->text($real_name,\Env::get('root_path').'/public/font/SourceHanSansCN.otf',60,'#000000',[400,1170])->save(\Env::get('root_path').'public/qrcode/user/'.$uid.'/'.$uid.$qr_config['img_name'].'.png');
        $image->text($time,\Env::get('root_path').'/public/font/SourceHanSansCN.otf',60,'#000000',[520,2450])->save(\Env::get('root_path').'public/qrcode/user/'.$uid.'/'.$uid.$qr_config['img_name'].'.png');
    }

}
