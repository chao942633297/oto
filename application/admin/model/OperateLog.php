<?php

namespace app\admin\model;

use think\Model;
#后台操作日志
class OperateLog extends Model
{
    // 确定链接表名
    protected $table = 'operate_log';

    #获取表操作sql语句记录
    public static function writeSqlLog($sql='')
    {	
    	$data = [];
    	$data['ip'] = request()->ip();
        $data['url']= request()->url();
    	$data['operate'] = $sql;
    	$data['uid'] = session('id');
    	$data['created_at'] = date('Y-m-d H:i:s');
        #写入操作
    	self::create($data);
    }




}