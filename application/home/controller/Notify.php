<?php

namespace app\home\controller;



use think\Controller;
use think\Db;
use wechatH5\Notify_pub;

vendor('wechatH5.WxMainMethod');
class Notify extends Controller{




    public function wechatNotify(){
        $notify = new Notify_pub();
        $xml = file_get_contents("php://input");
        //写入日志
        $notify->log_result('notify_url.log', $xml);
        $notify->saveData($xml);
        if ($notify->checkSign() == TRUE) {     //验签
            $returnData = $notify->xmlToArray($xml);
            $out_trade_no = $returnData['out_trade_no'];   //订单号
            $order = Db::table('order')->where('pay_order_num',$out_trade_no)->find();
            $total_fee = $returnData['total_fee'] / 100;    //实付金额
            if ($order['status'] == 1) {

            }
        }
    }




}
