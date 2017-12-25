<?php

namespace app\admin\controller;
use app\admin\model\NodeModel;
use app\admin\model\Order as Orders;
use app\home\model\CashRecord;
use app\admin\model\UsersModel;
use app\admin\model\UserMoneyLog;
use think\Db;


class Index extends Base
{
    public function index()
    {
        // 获取权限菜单
        $node = new NodeModel();
        $this->assign([
            'menu' => $node->getMenu(session('rule'))
        ]);

        return $this->fetch('/index');
    }

    /**
     * 后台默认首页
     * @return mixed
     */
    public function indexPage()
    {   
        $t = time();
        $start_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));  //当天开始时间
        $end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t)); //当天结束时间

        #今日注册用户数量
        $totalUser = Db::table('users')->count();
        $dayUser = Db::table('users')->whereTime('created_at','today')->count();

        #今日订单数量
        $totalOrder = Db::table('order')->where('status','>',1)->count();
        $dayOrder = Db::table('order')->whereTime('created_at','today')->where('status','>',1)->count();

        #订单总额
        $totalOrderMoney = Db::table('order')->where('status','>',1)->sum('real_price');
        #今日订单金额
        $dayOrderMoney = Db::table('order')->where('status','>',1)->whereTime('created_at','today')->sum('real_price');

        //会员余额
        $totalBalance = Db::table('users')->sum('balance');       //粮票
        $frozenPrice = Db::table('users')->sum('frozen_price');   //冻结粮票
        $rechargeCard = Db::table('users')->sum('recharge_card');   //充值卡余额
        $totalScore = Db::table('users')->where('is_union',1)->sum('score');   //用户积分
        $shopScore = Db::table('users')->where('is_union',2)->sum('score');   //商家积分

        #总发放红包金额
        $redMoney = UserMoneyLog::where('type',">=",7)->sum('money');

        #今日发放红包金额
        $todayRedMoney = UserMoneyLog::where('type',">=",7)->whereBetween('created_at',[$start_time,$end_time])->sum('money');

        //提现统计
        $withdrawMoney = Db::table('withdrawals')->where('status',2)->sum('real_money');
        $dayWithdraw = Db::table('withdrawals')->whereTime('created_at','today')->where('status',2)->sum('real_money');
        $reject = Db::table('withdrawals')->whereTime('created_at','today')->where('status',3)->sum('real_money');

        $this->assign('totalUser',$totalUser);
        $this->assign('dayUser',$dayUser);
        $this->assign('totalOrder',$totalOrder);
        $this->assign('dayOrder',$dayOrder);
        $this->assign('totalOrderMoney',$totalOrderMoney);
        $this->assign('dayOrderMoney',$dayOrderMoney);
        $this->assign('totalBalance',$totalBalance);
        $this->assign('frozenPrice',$frozenPrice);
        $this->assign('rechargeCard',$rechargeCard);
        $this->assign('totalScore',$totalScore);
        $this->assign('shopScore',$shopScore);
        $this->assign('redMoney',$redMoney);
        $this->assign('todayRedMoney',$todayRedMoney);
        $this->assign('withdrawMoney',$withdrawMoney);
        $this->assign('dayWithdraw',$dayWithdraw);
        $this->assign('reject',$reject);
        return $this->fetch('index');
    }

    #获取金额
    public static function getMoney()
    {   
        $time = [];

        for($i=1;$i<=12;$i++){

          $time[] = self::monthBeginEnd(date('Y'),$i);

        }

        $data = [];
        foreach ($time as $k => $v ) {
          $data[$k] = Orders::where('status','<>',Orders::ONE) ->where('created_at','between',$v)-> sum('real_price');
        }

        return $data;
    }

    #支出金额
    public static function putMoney()
    {   
        $time = [];

        for($i=1;$i<=12;$i++){

          $time[] = self::monthBeginEnd(date('Y'),$i);

        }

        $data = [];
        foreach ($time as $k => $v ) {
          $data[$k] = CashRecord::where('status',CashRecord::TWO) ->where('created_at','between',$v)-> sum('real_money');
        }

        return $data;
    }

    #获取类别用户数量
    public static function getUserTypeNum()
    {
        $data = [];
        $data[0] = UsersModel::where('type',UsersModel::SHOP)->count();
        $data[1] = UsersModel::where('type',UsersModel::BROTHER)->count();
        $data[2] = UsersModel::where('type',UsersModel::AVERAGEUSER)->count();
        $data[3] = UsersModel::where('power_code',2)->count();
        return $data;
    }

    #获取会员是否消费
    public static function getUserIsPay()
    {
        $data = [];
        $data[0] = UsersModel::where('is_xf',2)->count();
        $data[1] = UsersModel::where('is_xf',1)->count();
        return $data;
    }



    #订单管理
    public static function order()
    {   
        $week = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $time = [];
        #获取一周内的每天的开始结束时间
        foreach ($week as $k => $v) {
            $time[$k] = self::weekBeginEnd($v);
        }

        $data = [];
        foreach ($time as $k1 => $v1) {
            $data[] = Orders::where('status','<>',1)->where('created_at','between',$v1)->count();
        }

        return $data;
    }

    # 返回月初月末时间戳

    public static function monthBeginEnd($y='2017',$m){

        # 当前年月

        $month = $y."-".$m;

        # 指定月份月初时间戳

        $data[] = strtotime($month);

        # 指定月份月末时间戳

        $data[] = mktime(23, 59, 59, date('m', strtotime($month))+1, 00);

        return $data;

    }


    /** 
     * 返回本周每天的开始和结束的时间戳 
     * 
     *@param $week  周几
     * @return array 
     */  
    public static function weekBeginEnd($week)  
    {  
        $timestamp = time();  
        return [  
            strtotime(date('Y-m-d', strtotime("this week ".$week, $timestamp))),
            strtotime(date('Y-m-d', strtotime("this week ".$week, $timestamp))) + 24 * 3600 - 1  
        ];  
    } 
}
