<?php

namespace app\admin\controller;
use app\admin\model\NodeModel;
use app\admin\model\Orders;
use app\home\model\CashRecord;
use app\admin\model\UsersModel;


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
        // #平台收到的钱
        // $getMoney = self::getMoney();
        
        // #平台支出的钱
        // $putMoney = self::putMoney();

        // #获取用户类别所占人数
        // $user = self::getUserTypeNum();

        // #获取消费用户/未消费用户
        // $payUser = self::getUserIsPay();

        // #获取一周之内订单数量走向
        // $order = self::order();

        // $this->assign('getMoney',$getMoney);
        // $this->assign('putMoney',$putMoney);
        // $this->assign('user',$user);
        // $this->assign('payUser',$payUser);
        // $this->assign('order',$order);
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
