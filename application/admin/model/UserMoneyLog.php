<?php

namespace app\admin\model;

use think\Model;
#用户管理模型
class UserMoneyLog extends Base
{
    // 确定链接表名
    protected $table = 'user_money_log';
    #金额状态
    const STATUS = [1=>'正常',2=>'冻结'];

    #金额类别
    const TYPE = [1=>'分享奖',2=>'感恩奖',3=>'共享奖',5=>'购买商品',6=>'粮票提现',7=>'登录红包',8=>'分享红包',9=>'购物红包'];
    

    #关联users表
    public function user()
    {
        return $this->belongsTo('UsersModel','uid','id');
    }
    #关联users表
    public function sources()
    {
        return $this->belongsTo('UsersModel','source','id');
    }
    #获取会员粮票
    public static function getBalance($id,$status=1)
    {
        $add = self::where(['uid'=>$id,'is_add'=>1,'status'=>$status])->sum('money');
        $reduce = self::where(['uid'=>$id,'is_add'=>2,'status'=>$status])->sum('money');
        return sprintf("%.2f",$add - $reduce);
    }

    #获取类型余额
    public static function getTypeBalance($id,$type)
    {
        return sprintf("%.2f",self::where(['uid'=>$id,'type'=>$type,'is_add'=>1])->sum('money'));
    }   

    #获取类型余额记录
    public static function getTypeBalanceList($id,$type)
    {
        return self::where(['uid'=>$id,'type'=>$type,'is_add'=>1])->select();
    }    
}