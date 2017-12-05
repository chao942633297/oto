<?php

namespace app\admin\model;

use think\Model;
#充值卡模型
class Rechargeables extends Base
{
    // 确定链接表名
    protected $table = 'recharge_card';
    
    const STATUS = [1=>'未出售',2=>'未使用',3=>'已使用'];
}