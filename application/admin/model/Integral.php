<?php

namespace app\admin\model;

use think\Model;
#积分模型
class Integral extends Base
{
    // 确定链接表名
    protected $table = 'integral';

    #积分类别
    const TYPE = [1=>'消费积分',2=>'店铺积分'];

    const SOURCE = [1=>'粮票提现',2=>'兑换积分',3=>'消费购物',4=>'店铺积分提现'];

    #关联users表
    public function user()
    {
        return $this->belongsTo('UsersModel','uid','id');
    }

    #获取会员类型积分
    public static function getTypeBalance($id,$type=1)
    {
        $add = self::where(['uid'=>$id,'is_add'=>1,'type'=>$type])->sum('value');
        $reduce = self::where(['uid'=>$id,'is_add'=>2,'type'=>$type])->sum('value');
        return sprintf("%.2f",$add - $reduce);
    }

    #获取类型积分记录
    public static function getTypeBalanceList($id,$type,$is)
    {
        return self::where(['uid'=>$id,'type'=>$type,'is_add'=>$is])->select();
    }    
 
}