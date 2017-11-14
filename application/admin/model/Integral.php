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
 
}