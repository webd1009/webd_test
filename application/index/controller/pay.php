    public function pay_fund_one()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\User::get_user_info($user_id);
        if ($user_info['fund_one'] >= 998) return ['code'=>0,'msg'=>'只能购买一次'];
        $pay_fund_one = \app\index\model\Pay::pay_fund($user_info,$type = 1);
        switch ($pay_fund_one)
        {
            case 'success':
                return ['code'=>1,'msg'=>'购买成功'];
                break;
            case 'error':
                return ['code'=>0,'msg'=>'系统错误,请联系客服!'];
                break;
            case 'error_status':
                return ['code'=>0,'msg'=>'该账户已被禁用'];
                break;
        }
    }

    public function pay_fund_two()
    {
        $user_id = Session::get('user_id');
        $user_info = \app\index\model\User::get_user_info($user_id);
        if ($user_info['fund_two'] >= 1398) return ['code'=>0,'msg'=>'只能购买一次'];
        $pay_fund_one = \app\index\model\Pay::pay_fund($user_info,$type = 2);
        switch ($pay_fund_one)
        {
            case 'success':
                return ['code'=>1,'msg'=>'购买成功'];
                break;
            case 'error':
                return ['code'=>0,'msg'=>'系统错误,请联系客服!'];
                break;
            case 'error_status':
                return ['code'=>0,'msg'=>'该账户已被禁用'];
                break;
        }
    }
