<?php

namespace app\home\controller;


use app\admin\model\GoodsModel;
use app\admin\model\Order;
use think\Controller;
use think\Db;
use think\Exception;
use think\Request;

class Orders extends Base
{


    protected $userId;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->userId = session('uid');
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
            $return['addrId'] = $addrData['id'];
            $return['name'] = $addrData['name'];
            $return['phone'] = $addrData['phone'];
            $return['address'] = $addrData['province'] . $addrData['city'] . $addrData['area'] . $addrData['address'];
        }
        //TODO:充值卡余额 /用户积分
        $money = 0;
        $order_type = 1;         //普通订单
        if ($type == 1) {
            $money = Db::table('users')->where('id', $this->userId)->value('recharge_card');
            $order_type = 2;      //排位订单
        } else if ($type == 2) {
            $money = Db::table('users')->where('id', $this->userId)->value('score');
        }
        //判断是否设置支付密码
        $is_payPwd = 0;
        if (Db::table('users')->where('id', $this->userId)->value('pay_password')) {
            $is_payPwd = 1;
        }
        return json(['data' => ['addr' => $return, 'shop' => $shop,], 'totalPrice' => $totalPrice, 'money' => $money, 'type' => $order_type, 'is_payPwd' => $is_payPwd, 'msg' => '查询成功', 'code' => 200]);
    }


    /**
     * 立即支付
     */
    public function payment(Request $request)
    {
        $input = $request->post();
        if (empty($input['addrId'])) {
            return json(['msg' => '地址不能为空', 'code' => 2000]);
        }
        $user = Db::table('users')->where('id', $this->userId)->find();
        if (empty($user['pay_password'])) {
            return json(['msg' => '请先设置支付密码', 'code' => 1010]);
        }
        $msg = isset($input['msg']) ? htmlspecialchars($input['msg']) : '';
        $payment = $request->param('payment');
        if (!in_array($payment, [1, 2, 3, 4, 5])) {
            return json(['msg' => '支付方式错误', 'code' => 1001]);
        }
        $type = $request->param('type') ?: 1;           //订单类型 1 普通订单 2 排位订单
        if (empty($input['goodId']) || empty($input['goodNum'])) {
            return json(['msg' => '参数错误', 'code' => 1001]);
        }
        $pay_type = '';
        $remark = '';
        $userMoney = '';
        if (in_array($payment, [1, 4, 5])) {           // 1,4,5 支付方式需判断支付密码
            if ($payment == 1) {
                $pay_type = 'recharge_card';
                $remark = '充值卡金额不足';
            } else if ($payment == 4) {
                $pay_type = 'score';
                $remark = '积分不足';
            } else {
                $pay_type = 'balance';
                $remark = '粮票不足';
            }
            if (empty($input['password']) || md5($input['password']) !== $user['pay_password']) {
                return json(['msg' => '支付密码不正确', 'code' => 1003]);
            }
            $userMoney = $user[$pay_type] ?: '';
        }
        $result = $this->saveOrder($input['goodId'], $input['goodNum'], $msg, $type, $payment, $input['addrId'], $userMoney);
        if ($result == 'error_num') {
            return json(['商品数量不足', 'code' => 1003]);
        }
        if ($result == 'error_money') {
            return json(['msg' => $remark, 'code' => 1003]);
        }
        if ($result) {
            session('home_good_id', '');
            session('home_good_num', '');
            switch ($payment) {                   //1 充值卡支付 2支付宝支付 3 微信支付 4积分支付
                case 1 :
                    //充值卡支付
                    $res = Db::table('users')->where('id', $user['id'])->setDec($pay_type, $result['price']);
                    if ($res) {
                        return json(['msg' => '购买成功', 'code' => 200]);
                    }
                    return json(['msg' => '购买失败', 'code' => 1001]);
                    break;
                case 2 :
                    //支付宝
                    $ali_url = "http://" . config('back_url') . "/home/Alipay/webPay?orderId=" . $result['id'];
                    return json(['data' => $ali_url, 'msg' => '支付宝支付', 'code' => 200]);
                    break;
                case 3 :
                    //微信支付
                    $wechat_url = "http://" . config('back_url') . "/home/wxpay/wechatPay?orderId=" . $result['id'];
                    return json(['data' => $wechat_url, 'msg' => '微信支付', 'code' => 200]);
                    break;
                case 4 :
                    //积分支付
                    $res = Db::table('users')->where('id', $user['id'])->setDec($pay_type, $result['price']);
                    if ($res) {
                        return json(['msg' => '购买成功', 'code' => 200]);
                    }
                    return json(['msg' => '购买失败', 'code' => 1001]);
                    break;
            }
        }
        return json(['msg' => '购买失败', 'code' => 1002]);
    }

    /**
     * 保存订单
     */
    public function saveOrder($strId, $strNum, $msg, $type, $payment, $addrId, $userMoney)
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
            if ($shop[$key]['num'] > $good['num']) {         //购买数量超过商品库存
                return 'error_num';
            }
            $totalNum += (int)$goodNum[$key];
            $totalPrice += $good['price'] * (int)$goodNum[$key];
        }
        if (in_array($payment, [1, 4, 5])) {
            if (!empty($userMoney) && $totalPrice > $userMoney) {
                return 'error_money';
            }
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
                $goodData = [];
                foreach ($goodId as $key => $val) {
                    $shop[$key]['order_id'] = $res['id'];
                    //扣除商品库存
                    $goodData[$key] = [
                        'id' => $val,
                        'num' => ['exp', 'num - ' . $goodNum[$key]],
                    ];
                }
                $good = new GoodsModel();
                $good->saveAll($goodData);
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
            return $e->getMessage();
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
        $order = Order::all(['uid' => $this->userId, 'status' => $status,'is_score'=>1]);
        $return = [];
        foreach ($order as $key => $val) {
            $return[$key]['id'] = $val['id'];
            $return[$key]['payment'] = $val['payment'];
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
        $return['payment'] = $order['payment'];
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


    public function paynow(Request $request)
    {
        $payment = $request->param('payment');
        $orderId = $request->param('orderId');
        if (empty($payment) || empty($orderId)) {
            return json(['msg' => '参数错误', 'code' => 1001]);
        }
        $url = '';
        if ($payment == 2) {
            //支付宝支付
            $url = "http://" . config('back_url') . "/home/Alipay/webPay?orderId=" . $orderId;
        } else if ($payment == 3) {
            //微信支付
            $url = "http://" . config('back_url') . "/home/wxpay/wechatPay?orderId=" . $orderId;
        }
        return json(['data' => $url, 'msg' => '成功', 'code' => 200]);
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


    /**
     * 用户成为合伙人
     * 冻结金额转化余额
     */
    public function frozenChange()
    {
        $user = Db::table('users')->where('id', $this->userId)->find();
        if ($user['type'] == 2 && $user['type_time'] <= date('Y-m-d H:i:s', time() - 86400)) {
            if ($user['frozen_price'] > 0) {
                $user['balance'] += $user['frozen_price'];
                $user['frozen_price'] = 0;
                $user->save();
            }
        }
    }

}