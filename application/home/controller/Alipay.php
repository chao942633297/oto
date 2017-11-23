<?php

namespace app\home\controller;

use think\Controller;
use think\Db;
use think\Request;
use Vendor\AliPay\AlipayTradeService;
use Vendor\AliPay\AlipayTradeWapPayContentBuilder;
use Vendor\AliPay\Config;

class Alipay extends Controller{


    public function webPay(Request $request){             //支付宝支付
        $orderId = $request->param('orderId');
        $order = Db::table('order')->where('id',$orderId)->find();
        $body = '购物';
        $subject = '欧凸欧商城';
        $out_trade_no = $order['pay_order_num'];
        $total_amount = $order['price'];
        #测试金额  #TODO
        $total_amount = 0.1;

        $timeout_express = '1m';
        $config = Config::config();
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);
        $payResponse = new AlipayTradeService($config);
        $payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);
    }



}