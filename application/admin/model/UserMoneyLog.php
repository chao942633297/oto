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
    const TYPE = [1=>'红包',2=>'分销佣金',3=>'购买商品',4=>'粮票提现',5=>'',6=>''];
    

    #关联users表
    public function users()
    {
        return $this->belongsTo('UsersModel','uid','id');
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
}