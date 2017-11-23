<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\Order as Orders;
use app\admin\model\UsersModel;

#订单控制器
class Order extends Controller
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
                $selectResult[$key]['person'] = $vo->orderInfo['name'];
                $selectResult[$key]['address'] = $vo->orderInfo['province'].$vo->orderInfo['city'].$vo->orderInfo['area'].$vo->orderInfo['address'];
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'], $vo['status']));
                #订单状态
                $selectResult[$key]['status'] = $vo['status'] ? Orders::STATUS[$vo['status']] :'无状态';
                #订单类型
                $selectResult[$key]['type'] = $vo['type'] ? Orders::TYPE[$vo['type']] :'无类型';
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
        $this->assign('type',Orders::TYPE); //订单类型
		return $this->fetch();
	}

	#订单详情
	public function order_detail()
	{
		$id = input('param.id');
		
		#查出此订单信息
		$info = Orders::getOneInfo($id);
		#关联查出订单详情
		$orderDetail = $info->orderDetail;

		return json(['info'=>$info,'orderDetail'=>$orderDetail]);
	}

    #修改订单状态
    public function order_status()
    {
        $id = input('param.id');

        $flag = Orders::where('id',$id)->update(['status'=>3]);

        if ($flag) {
            $this->success('操作成功',url('order/index'));
        }
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id,$type)
    {   
        if ($type == 2) {
            return [
                '详情' => [
                    'auth' => 'order/order_detail',
                    'href' => "javascript:order_detail(" .$id .")",
                    'btnStyle' => 'btn btn-warning btn-ms',
                    'icon' => 'fa fa-paste'
                ],
                '发货' => [
                    'auth' => 'order/order_status',
                    'href' => url('order/order_status',['id'=>$id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ]
            ];
        }
        return [
            '详情' => [
                'auth' => 'order/order_detail',
                'href' => "javascript:order_detail(" .$id .")",
                'btnStyle' => 'btn btn-warning btn-ms',
                'icon' => 'fa fa-paste'
            ]
        ];             
    }


    public function excel()
    {
            $param = input('param.');

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
            $selectResult = $order->where($where)->select();

            $data = [];
            $data[0] = ["id"=>'序号','pay_order_num'=>'订单编号','username'=>'购买人','userphone'=>'联系方式','price'=>'订单金额','real_price'=>'实付金额','status'=>'订单状态','payment'=>'支付方式','type'=>'订单类型','goods'=>'购买商品','address'=>'收货地址','created_at'=>'下单时间'];
            foreach ($selectResult as $k => $v) {
                $data[$k+1]['id'] = $v->id;  
                $data[$k+1]['pay_order_num'] = $v->pay_order_num;    
                $data[$k+1]['username'] = $v->user->nickname;    
                $data[$k+1]['userphone'] = $v->user->phone;    
                $data[$k+1]['price'] = $v->price;    
                $data[$k+1]['real_price'] = $v->real_price;    
                $data[$k+1]['status'] = Orders::STATUS[$v->status];    
                $data[$k+1]['real_price'] = $v->real_price;    
                $data[$k+1]['payment'] = Orders::PAYMENT[$v->payment];
                $data[$k+1]['type'] = Orders::TYPE[$v->type];    
                // $orderDetail = json_decode(json_encode($v->orderDetail),true) ; 
                $orderDetail = $v->orderDetail;
                $orderInfo = $v->orderInfo;    
                $data[$k+1]['goods'] = '';  
                foreach ($orderDetail as $key => $value) {
                    $num = $key+1;
                    $str = "第".$num."个商品".$value->name."总价".($value->price*$value->num)."元\n";
                    $data[$k+1]['goods'] .= $str;
                }

                $data[$k+1]['address'] = $orderInfo->province.$orderInfo->city.$orderInfo->area.$orderInfo->address;    

                $data[$k+1]['created_at'] = date('Y-m-d H:i:s',$v->created_at);    

            }

            put_excel("订单列表",$data);
    }


}
