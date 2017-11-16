<?php

namespace app\admin\model;

use think\Model;
#用户管理模型
class UsersModel extends Base
{
    // 确定链接表名
    protected $table = 'users';
    
    const TYPE = [1=>'普通用户',2=>'合伙人'];

    public function father()
    {
    	return $this->belongsTo('UsersModel','pid','id');
    }
}