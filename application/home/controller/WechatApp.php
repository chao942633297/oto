<?php
namespace app\home\controller;
use think\Controller;
use think\Exception;

header("Content-type:text/html;charset=utf-8");

class WechatApp extends Controller{

	private $appid='wxa0f4b580b186a340';//开放平台appid

	private $mch_id='1480209132';//开放平台对应的商户id

	private $key='78b999977113107471252346e5c8959b';//开放平台对应的商户秘钥

	private $trade_type="APP";

	private $notify_url='http://ht.xtwhjy.com/Home/Not/wechat';//通知地址
    public function wechatAppPay(){
        $input = I('get.');
        $orderId = $input['orderId'];
        $type = $input['type'];
        $this->assign('orderId',$orderId);
        $this->assign('type',$type);
        $this->display('Wechat/wechatAppPay');
    }



	 public function setSendData($order,$price){

        $this->sTpl = "<xml>

                            <appid><![CDATA[%s]]></appid>

                            <body><![CDATA[%s]]></body>

                            <mch_id><![CDATA[%s]]></mch_id>

                            <nonce_str><![CDATA[%s]]></nonce_str>

                            <notify_url><![CDATA[%s]]></notify_url>

                            <out_trade_no><![CDATA[%s]]></out_trade_no>

                            <spbill_create_ip><![CDATA[%s]]></spbill_create_ip>

                            <total_fee><![CDATA[%d]]></total_fee>

                            <trade_type><![CDATA[%s]]></trade_type>

                            <sign><![CDATA[%s]]></sign>

                        </xml>";                          //xml数据模板



        $nonce_str = $this->getNonceStr();        //调用随机字符串生成方法获取随机字符串



        $data['appid'] = $this->appid;

        $data['mch_id'] = $this->mch_id;

        $data['nonce_str'] = $nonce_str;

        $data['notify_url'] = $this->notify_url;

        $data['trade_type'] = $this->trade_type;      //将参与签名的数据保存到数组

        $data['body']='杏坛文化教育';

        $data['out_trade_no']=$order;

        $data['spbill_create_ip']=get_client_ip();

        $data['total_fee']= $price*100;

        // 注意：以上几个参数是追加到$data中的，$data中应该同时包含开发文档中要求必填的剔除sign以外的所有数据
        $sign = $this->getSign($data);        //获取签名
        // return $data;

        $date = sprintf($this->sTpl, $this->appid, $data['body'], $this->mch_id,

            $nonce_str, $this->notify_url, $data['out_trade_no'], $data['spbill_create_ip'],

            $data['total_fee'], $this->trade_type, $sign);

        //生成xml数据格式

        return $date;

    }



    /**

     * @return mixed|string

     */

    public function sendRequest(){//发送请求

        $orderId=I('get.orderId');
        $type = I('get.type');
        $order = D('OrderRelation');
        $orderData = $order->where(array('id'=>$orderId))->relation(true)->find();
        if($orderData){
            $out_trade_no = $orderData['ordercode'];
            if($orderData['course']){
                if($orderData['user2']['grade'] >= 3){
                    $total_fee = $orderData['goodprice'] * $orderData['course']['discount'] * 0.01;
                }else{
                    $total_fee = $orderData['goodprice'];
                }
            }else if($orderData['good']){
                if($orderData['user2']['grade'] >=3){
                    $total_fee = $orderData['good']['discount'] * $orderData['goodnum'];
                }else{
                    $total_fee = $orderData['goodprice'] * $orderData['goodnum'];
                }
            }else{
                $total_fee = $orderData['money'];
            }
            // $total_fee = 0.01;
            if($type == 'surplus' && $orderData['status'] ==1){
                D('Order')->where(array('id'=>$orderId))->setField('paytype','surplus');
                $result = A('Balance')->getAllMoney($orderId);
                $total_fee = $result['stillNeedMoney'];
                // $total_fee = 0.02;
            }

        }else{
            $this->ajaxReturn(['status'=>false,'error_message'=>'没有该订单']);

            exit;
        }
        $xmlStr=$this->setSendData($out_trade_no,$total_fee);
        $sendUrl = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

        $result = $this->postXmlCurl($xmlStr,$sendUrl);
        $postObj = $this->xmlToObject($result);            //解析返回数据
        // $this->ajaxReturn(['status'=>true,'error_message'=>'返回数据成功','data'=>$postObj]);

        if ($postObj === false) {

            echo 'FAIL';

            exit;             // 如果解析的结果为false，终止程序

        }



        if ($postObj->return_code == 'FAIL') {

            echo $postObj->return_msg;            // 如果微信返回错误码为FAIL，则代表请求失败，返回失败信息；

        } else {

            //如果上一次请求成功，那么我们将返回的数据重新拼装，进行第二次签名

            $resignData = array(

                'appid' => $postObj->appid,

                'partnerid' => $postObj->mch_id,

                'prepayid' => $postObj->prepay_id,

                'noncestr' => $postObj->nonce_str,

                'timestamp' => time(),

                'package' => 'Sign=WXPay'

            );

            //二次签名；

            $sign = $this -> getClientPay($resignData);

            $resignData['sign'] = $sign;

            // $resignData

            $jsApiParameters = json_encode($resignData);
            // dump($jsApiParameters);
            $this->ajaxReturn(['status'=>true,'error_message'=>'返回数据成功','data'=>$jsApiParameters]);
// jsonpReturn(true,'返回数据成功',$jsApiParameters);

        }

    }

    /**

     * 获取客户端支付信息

     * @param  Array $data 参与签名的信息数组

     * @return String       签名字符串

     */

    public function getClientPay($data) {
        $sign = $this->getSign($data);        // 生成签名并返回

        return $sign;

    }



    /**

     * 解析xml文档，转化为对象

     * @author 栗荣发 2016-09-20

     * @param  String $xmlStr xml文档

     * @return Object         返回Obj对象

     */

    public function xmlToObject($xmlStr) {

        if (!is_string($xmlStr) || empty($xmlStr)) {

            return false;

        }

        // 由于解析xml的时候，即使被解析的变量为空，依然不会报错，会返回一个空的对象，所以，我们这里做了处理，当被解析的变量不是字符串，或者该变量为空，直接返回false

        $postObj = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        $postObj = json_decode(json_encode($postObj));

        //将xml数据转换成对象返回

        return $postObj;

    }



    private function postXmlCurl($xml, $url, $useCert = false, $second = 30){

        $ch = curl_init();

        //设置超时

        curl_setopt($ch, CURLOPT_TIMEOUT, $second);



        //如果有配置代理这里就设置代理

        curl_setopt($ch,CURLOPT_URL, $url);

        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);

        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验

        //设置header

        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        //要求结果为字符串且输出到屏幕上

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);



        if($useCert == true){

            //设置证书

            curl_setopt($ch,CURLOPT_SSLCERT,'./ThinkPHP/Library/Vendor/Weixinpay/cacert/apiclient_cert.pem');

            curl_setopt($ch,CURLOPT_SSLKEY,'./ThinkPHP/Library/Vendor/Weixinpay/cacert/apiclient_key.pem');

            curl_setopt($ch,CURLOPT_CAINFO,'./ThinkPHP/Library/Vendor/Weixinpay/cacert/rootca.pem');

        }

        //post提交方式

        curl_setopt($ch, CURLOPT_POST, TRUE);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        //运行curl

        $data = curl_exec($ch);

        //返回结果
        if($data){

            curl_close($ch);

            return $data;

        }else{

            $error = curl_errno($ch);

            curl_close($ch);

            return "curl出错，错误码:$error";

        }

    }



    /**

     * 获取参数签名；

     * @param  Array  要传递的参数数组

     * @return String 通过计算得到的签名；

     */

    private function getSign($params) {

        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序

        foreach ($params as $key => $item) {

            if (!empty($item)) {         //剔除参数值为空的参数

                $newArr[] = $key.'='.$item;     // 整合新的参数数组

            }

        }

        $stringA = implode("&", $newArr);         //使用 & 符号连接参数

        $stringSignTemp = $stringA."&key=".$this->key;        //拼接key

        // key是在商户平台API安全里自己设置的

        $stringSignTemp = md5($stringSignTemp);       //将字符串进行MD5加密

        $sign = strtoupper($stringSignTemp);      //将所有字符转换为大写

        return $sign;

    }



    /**

     *生成随机数

     */

    private function getNonceStr()

    {

        $code = '';

        for ($i = 0; $i > 10; $i++) {

            $code.=mt_rand(1000);//获取随机数

        }

        $nonceStrTemp=md5($code);

        $nonce_str=mb_substr($nonceStrTemp,5,37);//MD5加密后截取32位字符串

        return $nonce_str;

    }





    /**

    *接收通知

    */

    public function replyWx()

    {

    	$postXml = $GLOBALS["HTTP_RAW_POST_DATA"];    // 接受通知参数；

	    if (empty($postXml)) {

	        return false;

	    }

	    $postObj = $this->xmlToObject($postXml);      // 调用解析方法，将xml数据解析成对象

	    if ($postObj === false) {

	        return false;

	    }

	    if (!empty($postObj->return_code)) {

	        if ($postObj->return_code == 'FAIL') {

	            return false;

	        }

	    }

	    return $postObj;          // 返回结果对象；

    }



    /**

    *微信通知结果

    */

    public function notify()//验证确实是微信发来的数据然后返回给微信结果

    {

    	$obj=$this->replyWx();

    	

    	if ($obj) {

		    $content=$this->queryOrder($obj->out_trade_no);

		    if($content->return_code=="SUCCESS"&&$content->result_code=="SUCCESS"){

		    	$res=$this->appPayInfo($content);

		    	if($res){

					$reply = "<xml>

		                    <return_code><![CDATA[SUCCESS]]></return_code>

		                    <return_msg><![CDATA[OK]]></return_msg>

		                </xml>";

			        echo $reply;      // 向微信后台返回结果。

			        exit;

		    	}else{

		    		echo 'FAIL';

	        	exit;

		    	}

		    }else{

		    	echo 'FAIL';

	        	exit;

		    }		    

		}else{

			echo 'FAIL';

	        exit;

		}

    }


    /**

	 * 查询订单真实性

	 * @param  string $out_trade_no 订单号

	 * @return xml               订单查询结果

	 */

	public function queryOrder($out_trade_no) {

	    $nonce_str = $this->getNonceStr();

	    $data = array(

	        'appid'        =>    $this->appid,

	        'mch_id'    =>    $this->mch_id,

	        'out_trade_no'    =>    $out_trade_no,

	        'nonce_str'            =>    $nonce_str

	        );

	    $sign = $this->getSign($data);

	    $xml_data = '<xml>

	                   <appid>%s</appid>

	                   <mch_id>%s</mch_id>

	                   <nonce_str>%s</nonce_str>

	                   <out_trade_no>%s</out_trade_no>

	                   <sign>%s</sign>

	                </xml>';

	    $xml_data = sprintf($xml_data, $this->appid, $this->mch_id, $nonce_str, $out_trade_no, $sign);

	    $url = "https://api.mch.weixin.qq.com/pay/orderquery";

	    $result = $this->postXmlCurl($xml_data,$url);

        $content = $this->xmlToObject($result);            //解析返回数据

	    // $content = $curl->execute(true, 'POST', $xml_data);

	    return $content;

	}

}

?>