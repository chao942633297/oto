<?php

namespace app\home\controller;


use app\admin\model\Order;
use think\Controller;
use think\Db;
use think\Exception;
use think\Request;

class Orders extends Controller
{


    protected $userId;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->userId = 1;
    }


    /**
     *确认订单
     */
    public function sureOrder(Request $request)
    {
        $strId = $request->param('goodId');
        $strNum = $request->param('goodNum');
        //若商品id为空,则获取session的数据
        if (empty($strId) || empty($strNum)) {
            $strId = session('home_good_id');
            $strNum = session('home_good_num');
        } else {
            session('home_good_id', $strId);
            session('home_good_num', $strNum);
        }
        if (empty($strId) || empty($strNum)) {
            return json(['msg' => '参数错误', 'code' => 1001]);
        }
        $goodId = explode(',', rtrim($strId, ','));
        $goodNum = explode(',', rtrim($strNum, ','));
        $shop = [];
        $totalPrice = 0;
        $type = 0;
        foreach ($goodId as $key => $val) {
            $good = Db::table('good')->where('id', $val)->find();
            $shop[$key]['goodName'] = $good['name'];
            $shop[$key]['goodImg'] = $good['img'];
            $shop[$key]['goodPrice'] = $good['price'];
            $shop[$key]['goodNum'] = $goodNum[$key];
            $type = $good['type'];
            $totalPrice += $good['price'] * $goodNum[$key];
        }

        //若传入,则使用传入的地址,否则使用用户默认地址
        $addressId = $request->param('addrId');
        if ($addressId) {
            $where['id'] = $addressId;
        } else {
            $where['uid'] = $this->userId;
        }
        $addrData = Db::table('address')
            ->where($where)
            ->order('is_default', 'desc')->find();
        $return = [];
        if ($addrData) {
            $return['name'] = $addrData['name'];
            $return['phone'] = $addrData['phone'];
            $return['address'] = $addrData['province'] . $addrData['city'] . $addrData['area'] . $addrData['address'];
        }
        //TODO:充值卡余额 /用户积分
        $money = 0;
        if ($type == 1) {
            $money = Db::table('users')->where('id', $this->userId)->value('recharge_card');
        } else if ($type == 2) {
            $money = Db::table('users')->where('id', $this->userId)->value('score');
        }


        return json(['data' => ['addr' => $return, 'shop' => $shop,], 'totalPrice' => $totalPrice, 'money' => $money, 'type' => $type, 'msg' => '查询成功', 'code' => 200]);
    }


    /**
     * 立即支付
     */
    public function payment(Request $request)
    {
        $input = $request->post();
        $msg = htmlspecialchars($input['msg']);
        $payment = $request->param('payment');
        if (!in_array($payment, [1, 2, 3])) {
            return json(['msg' => '支付方式错误', 'code' => 1001]);
        }
        $type = $request->param('type');           //订单类型
        if (empty($input['goodId']) || empty($input['goodNum'])) {
            return json(['msg' => '参数错误', 'code' => 1001]);
        }
        $result = $this->saveOrder($input['goodId'], $input['goodNum'], $msg, $type, $payment, $input['addrId']);
        if ($result) {
            switch ($payment) {
                case 1 :
                    //充值卡支付
                    $user = Db::table('users')->find($this->userId);
                    if (md5($input['password'] !== $user['pay_password'])) {
                        return json(['msg' => '支付密码不正确', 'code' => 1002]);
                    }
                    if ($result['price'] > $user['recharge_card']) {
                        return json(['msg' => '充值卡余额不足', 'code' => 1002]);
                    }
                    if ($result == 'error_num') {
                        return json(['msg' => '商品数量错误', 'code' => 1002]);
                    }
                    //减少用户充值卡金额
                    $res = Db::table('users')->where('id', $user['id'])->setDec('recharge_card', $result['price']);
                    if ($res) {
                        return json(['msg' => '购买成功', 'code' => 200]);
                    }
                    break;
                case 2 :
                    //支付宝支付
                    break;
                case 3 :
                    //微信支付
                    break;
            }
        }

    }





    /**
     * 保存订单
     */
    public function saveOrder($strId, $strNum, $msg, $type, $payment, $addrId)
    {
        $goodId = explode(',', rtrim($strId, ','));
        $goodNum = explode(',', rtrim($strNum, ','));
        $shop = [];
        $totalPrice = 0;
        $totalNum = 0;
        foreach ($goodId as $key => $val) {
            $good = Db::table('good')->where('id', $val)->find();
            $shop[$key]['name'] = $good['name'];
            $shop[$key]['img'] = $good['img'];
            $shop[$key]['price'] = $good['price'];
            $shop[$key]['num'] = (int)$goodNum[$key];
            if ($shop[$key]['num'] <= 0) {
                return 'error_num';
            }
            $totalNum += (int)$goodNum[$key];
            $totalPrice += $good['price'] * (int)$goodNum[$key];
        }
        $order = [];
        $order['pay_order_num'] = order_sn();
        $order['uid'] = $this->userId;
        $order['price'] = $totalPrice;
        $order['total_num'] = $totalNum;
        $order['message'] = $msg;
        $order['type'] = $type;
        $order['status'] = 1;
        $order['payment'] = $payment;
        $order['created_at'] = time();
        Db::startTrans();
        try {
            $res = Order::create($order);   //保存订单
            if ($res) {
                $good = [];
                foreach ($goodId as $key => $val) {
                    $shop[$key]['order_id'] = $res['id'];
                    //扣除商品库存
                    $good[] = [
                        'id'    => $val,
                        'num'   => ['exp','num - 1'],
                    ];
                }
                Db::table('good')->update($good);
                $detail = Db::table('order_detail')
                    ->insertAll($shop);             //保存订单详情
                $addrData = Db::table('address')
                    ->field('province,city,area,address,phone,name')
                    ->where('id', $addrId)->find();
                $addrData['order_id'] = $res['id'];
                $addrData['created_at'] = time();
                $info = Db::table('order_info')//保存收货地址
                ->insert($addrData);
                if ($detail && $info) {
                    Db::commit();
                    return $res;        //返回订单数据
                }
            }
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }

    }


    /**
     * @param Request $request
     * @return \think\response\Json
     * 订单列表
     * 1代付款 2 待收货 3 代发货 4已完成
     */
    public function orderList(Request $request)
    {
        $status = $request->param('status');
        $order = Order::all(['uid' => $this->userId, 'status' => $status]);
        $return = [];
        foreach ($order as $key => $val) {
            $return[$key]['order_num'] = $val['pay_order_num'];
            $return[$key]['status'] = Order::STATUS[$val['status']];
            $return[$key]['good'] = $val['orderDetail'];
            $return[$key]['totalNum'] = $val['total_num'];
            $return[$key]['totalPrice'] = $val['price'];
        }
        return json(['data' => $return, 'msg' => '查询成功', 'code' => 200]);
    }


    /**
     * @param Request $request
     * @return \think\response\Json
     * 订单详情
     *传入订单id
     */
    public function orderDetail(Request $request)
    {
        $orderId = $request->param('orderId');
        $order = Order::get($orderId);
        $return = [];
        $return['id'] = $order['id'];
        $return['order_num'] = $order['pay_order_num'];
        $return['status'] = Order::STATUS[$order['status']];
        $return['address'] = $order['orderInfo'];
        $return['good'] = $order['orderDetail'];
        $return['price'] = $order['price'];
        $return['cour_code'] = $order['cour_code'];
        return json(['data' => $return, 'msg' => '查询成功', 'code' => 200]);
    }


    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws Exception
     * 取消订单
     * 传入订单id
     */
    public function cancelOrder(Request $request)
    {
        $orderId = $request->param('orderId');
        $res = Db::table('order')->where('id', $orderId)->delete();
        if ($res) {
            return json(['msg' => '取消订单成功', 'code' => 200]);
        }
        return json(['msg' => '取消订单失败', 'code' => 1001]);
    }


    /**
     * @param Request $request
     * @return \think\response\Json
     * @throws Exception
     * 确认收货
     * 传入订单id
     */
    public function sureGet(Request $request)
    {
        $orderId = $request->param('orderId');
        $res = Db::table('order')->where('id', $orderId)->update(['status' => 4]);
        if ($res) {
            return json(['msg' => '确认收货成功', 'code' => 200]);
        }
        return json(['msg' => '确认收货失败', 'code' => 1001]);
    }


}