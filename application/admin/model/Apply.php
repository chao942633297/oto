<?php

namespace app\admin\model;

use think\Model;
#申请联盟商家模型
class Apply extends Base
{
    // 确定链接表名
    protected $table = 'union_apply';
    const STATUS = [1=>'审核中',2=>'通过',3=>'驳回'];

    public function users()
    {
    	return $this->belongsTo('UsersModel','uid','id');
    }
}