<?php

namespace app\admin\model;

use think\Model;
#订单模型
class Order extends Base
{
    // 确定链接表名
    protected $table = 'order';

    #订单状态
    const STATUS = [1=>'待付款',2=>'待发货',3=>'发货中',4=>'已完成'];
    
    #订单类型
    const TYPE = [1=>'普通订单',2=>'公排订单'];

    #支付类型
    const PAYMENT = [1=>'支付宝',2=>'微信',3=>'粮票',4=>'充值卡',5=>'积分'];
    #关联用户
    public function user()
    {
    	return $this->belongsTo('UsersModel','uid','id');
    }

    #关联订单信息表
    public function orderInfo()
    {
    	return $this->HasOne('OrderInfo','order_id','id');
    }

    #关联订单详情表
    public function orderDetail()
    {
    	return $this->hasMany('OrderDetail','order_id','id');
    }
    
}