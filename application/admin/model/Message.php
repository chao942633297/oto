<?php

namespace app\admin\model;

use think\Model;
#用户消息模型
class Message extends Base
{
    // 确定链接表名
    protected $table = 'user_message';
 
 	public function user()
 	{
 		return $this->belongsTo('UsersModel','uid','id');
 	}   
}