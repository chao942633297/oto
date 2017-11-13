<?php
namespace app\admin\controller;

#财务对账控制器
class Finance extends Base
{
	public function index()
	{
		
		if ( request()->isAjax() ) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            #条件筛选
            if (!empty($param['pay_order_num'])) {
                $where['pay_order_num'] = $param['pay_order_num'];
            }

            if (!empty($param['phone'])) {
                $where['phone'] = ['like', '%' . trim($param['phone']) . '%'];
            }
            if (!empty($param['status']) && $param['status'] != '-1') {
                $where['status'] = trim($param['status']);
            }
            if (!empty($param['type']) && $param['type'] != '-1') {
                $where['type'] = trim($param['type']);
            }
            if (!empty($param['payment']) && $param['payment'] != '-1') {
                $where['payment'] = trim($param['payment']);
            }
            if (!empty($param['created_at']) ) {
                $where['created_at'] = ['>=',strtotime($param['created_at'])];
            }

            $order = new Orders();
            $selectResult = $order->getListByWhere($where, $offset, $limit);


            // 拼装参数
            foreach($selectResult as $key=>$vo){

            	$selectResult[$key]['username'] = $vo->user['nickname'];
            	$selectResult[$key]['userphone'] = $vo->user['phone'];
                $selectResult[$key]['person'] = $vo->orderInfo['person'];
                $selectResult[$key]['address'] = $vo->orderInfo['province'].$vo->orderInfo['city'].$vo->orderInfo['area'].$vo->orderInfo['address'];
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'], $vo['status']));
                #订单状态
                $selectResult[$key]['status'] = $vo['status'] ? Orders::STATUS[$vo['status']] :'无状态';
                #订单支付方式
                $selectResult[$key]['payment'] = $vo['payment'] ? Orders::PAYMENT[$vo['payment']] :'';
                #订单创建时间
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }

            $return['total'] = $order->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);			 
		}

		$this->assign('status',Orders::STATUS);   //订单状态
		$this->assign('payment',Orders::PAYMENT); //支付类型
		return $this->fetch();
	}
}