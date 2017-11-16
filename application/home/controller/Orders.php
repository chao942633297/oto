<?php

namespace app\home\controller;


use app\admin\model\Order;
use think\Controller;
use think\Db;
use think\Exception;
use think\Request;

class Orders extends Controller{


    protected $userId;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->userId = 1;
    }


    /**
     *确认订单
     */
    public function sureOrder(Request $request){
        $strId = $request->param('goodId');
        $strNum = $request->param('goodNum');
        //若商品id为空,则获取session的数据
        if(empty($strId) || empty($strNum)){
            $strId = session('home_good_id');
            $strNum = session('home_good_num');
        }else{
            session('home_good_id',$strId);
            session('home_good_num',$strNum);
        }
        if(empty($strId) || empty($strNum)){
            return json(['msg'=>'参数错误','code'=>1001]);
        }
        $goodId = explode(',',rtrim($strId,','));
        $goodNum = explode(',',rtrim($strNum,','));
        $shop = [];
        $totalPrice = 0;
        foreach($goodId as $key=>$val){
            $good = Db::table('good')->where('id',$val)->find();
            $shop[$key]['goodName'] = $good['name'];
            $shop[$key]['goodImg'] = $good['img'];
            $shop[$key]['goodPrice'] = $good['price'];
            $shop[$key]['goodNum'] = $goodNum[$key];
            $totalPrice += $good['price'] * $goodNum[$key];
        }

        //若传入,则使用传入的地址,否则使用用户默认地址
        $addressId = $request->param('addrId');
        if($addressId){
            $where['id'] = $addressId;
        }else{
            $where['uid'] = $this->userId;
        }
        $addrData = Db::table('address')
            ->where($where)
            ->order('is_default','desc')->find();
        $return = [];
        if($addrData){
            $return['name'] = $addrData['name'];
            $return['phone'] = $addrData['phone'];
            $return['address'] = $addrData['province'].$addrData['city'].$addrData['area'].$addrData['address'];
        }
        //TODO:充值卡余额

        return json(['addr'=>$return,'shop'=>$shop,'totalPrice'=>$totalPrice,'msg'=>'查询成功','code'=>200]);
    }


    /**
     * 立即支付
     */
    public function payment(Request $request){
        $input = $request->post();
        $msg = htmlspecialchars($input['msg']);
        $payment = $request->param('payment');
        if(!in_array($payment,[1,2,3])){
            return json(['msg'=>'支付方式错误','code'=>1001]);
        }
        $type = $request->param('type');           //订单类型
        if(empty($input['goodId']) || empty($input['goodNum'])){
            return json(['msg'=>'参数错误','code'=>1001]);
        }
        $result = $this->saveOrder($input['goodId'],$input['goodNum'],$msg,$type,$payment,$input['addrId']);
        if($result){
            switch($payment){
                case 1 :
                    //充值卡支付
                    $user = Db::table('users')->find($this->userId);
                    if(md5($input['password'] !== $user['pay_password'])){
                        return json(['msg'=>'支付密码不正确','code'=>1002]);
                    }
                    if($result['price'] > $user['recharge_card']){
                        return json(['msg'=>'充值卡余额不足','code'=>1002]);
                    }
                    if($result == 'error_num'){
                        return json(['msg'=>'商品数量错误','code'=>1002]);
                    }
                    //减少用户充值卡金额
                    $res = Db::table('users')->where('id',$user['id'])->setDec('recharge_card',$result['price']);
                    if($res){
                        return json(['msg'=>'购买成功','code'=>200]);
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
    public function saveOrder($strId,$strNum,$msg,$type,$payment,$addrId){
        $goodId = explode(',',rtrim($strId,','));
        $goodNum = explode(',',rtrim($strNum,','));
        $shop = [];
        $totalPrice = 0;
        foreach($goodId as $key=>$val){
            $good = Db::table('good')->where('id',$val)->find();
            $shop[$key]['name'] = $good['name'];
            $shop[$key]['img'] = $good['img'];
            $shop[$key]['price'] = $good['price'];
            $shop[$key]['num'] = (int)$goodNum[$key];
            if($shop[$key]['num'] <= 0){
                return 'error_num';
            }
            $totalPrice += $good['price'] * (int)$goodNum[$key];
        }
        $order = [];
        $order['pay_order_num'] = order_sn();
        $order['uid'] = $this->userId;
        $order['price'] = $totalPrice;
        $order['message'] = $msg;
        $order['type'] = $type;
        $order['status'] = 1;
        $order['payment'] = $payment;
        $order['created_at'] = time();
        Db::startTrans();
        try{
            $res = Order::create($order);   //保存订单
            if($res){
                foreach($goodId as $key=>$val){
                    $shop[$key]['order_id'] = $res['id'];
                }
                $detail = Db::table('order_detail')
                    ->insertAll($shop);             //保存订单详情
                $addrData = Db::table('address')
                    ->field('province,city,area,address,phone,name')
                    ->where('id',$addrId)->find();
                $addrData['order_id'] = $res['id'];
                $addrData['created_at'] = time();
                $info = Db::table('order_info')   //保存收货地址
                ->insert($addrData);
                if($detail && $info){
                    Db::commit();
                    return $res;        //返回订单数据
                }
            }
        }catch(Exception $e){
            Db::rollback();
            return false;
        }

    }




}