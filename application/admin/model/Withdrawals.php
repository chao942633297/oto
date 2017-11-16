<?php

namespace app\admin\model;

use think\Model;
#提现表模型
class Withdrawals extends Base
{
    // 确定链接表名
    protected $table = 'withdrawals';

    #提现类别
    const TYPE = [1=>'积分提现',2=>'粮票提现'];

    #订单状态
    const STATUS = [1=>'待审核',2=>'通过',3=>'驳回'];


    #关联users表
    public function user()
    {
        return $this->belongsTo('UsersModel','uid','id');
    }
 
}