<?php
namespace service;

class ChuanglanSmsApi{
	//创蓝发送短信接口URL, 请求地址请参考253云通讯自助通平台查看或者询问您的商务负责人获取
	const url = "http://smsbj1.253.com/msg/send/json";

	const API_VARIABLE_URL = "";

	const balance_url = "";

	//创蓝账号 替换成你自己的账号
	const account = "N4357536";

	//创蓝密码 替换成你自己的密码
	const password = "ByH7aSxoI16981";

	/**
	 * 发送短信
	 *
	 * @param string $mobile 		手机号码
	 * @param string $msg 			短信内容
	 * @param string $needstatus 	是否需要状态报告
	 */
	public function sendSMS( $mobile, $msg, $needstatus = 'true') {
		
		//创蓝接口参数
		$postArr = array (
			'account'  =>  self::account,
			'password' => self::password,
			'msg' => urlencode($msg),
			'phone' => $mobile,
			'report' => $needstatus
        );
		
		$result = $this->curlPost( self::url , $postArr);
		return $result;
	}
	
	/**
	 * 发送变量短信
	 *
	 * @param string $msg 			短信内容
	 * @param string $params 	最多不能超过1000个参数组
	 */
	public function sendVariableSMS( $msg, $params) {
		//创蓝接口参数
		$postArr = array (
			'account' => self::acount,
			'password' =>self::password,
			'msg' => $msg,
			'params' => $params,
			'report' => 'true'
        );
		
		$result = $this->curlPost( self::API_VARIABLE_URL, $postArr);
		return $result;
	}
	
	
	/**
	 * 查询额度
	 *
	 *  查询地址
	 */
	public function queryBalance() {
		//查询参数
		$postArr = array ( 
			'account' => self::acount,
			'password' =>self::password,
		);
		$result = $this->curlPost(self::balance_url, $postArr);
		return $result;
	}

	/**
	 * 通过CURL发送HTTP请求
	 * @param string $url  //请求URL
	 * @param array $postFields //请求参数 
	 * @return mixed
	 */
	private function curlPost($url,$postFields){
		$postFields = json_encode($postFields);
		$ch = curl_init ();
		curl_setopt( $ch, CURLOPT_URL, $url ); 
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8'
			)
		);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt( $ch, CURLOPT_TIMEOUT,1); 
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0);
		$ret = curl_exec ( $ch );
        if (false == $ret) {
            $result = curl_error(  $ch);
        } else {
            $rsp = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "请求状态 ". $rsp . " " . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
		curl_close ( $ch );
		return $result;
	}

	public static function sendCode($phone)
	{
		$clapi  = new ChuanglanSmsApi();
		$code = mt_rand(1000,9999);
		$result = $clapi->sendSMS($phone, '您的验证码是'.$code.'，请在10分钟内填写，切勿将验证码泄露于他人。【欧凸欧商城】');
		if(!is_null(json_decode($result))){
			$output=json_decode($result,true);
			if(isset($output['code'])  && $output['code']=='0'){

				return ['code'=>1,'msg'=>'发送成功','data'=>$code];
			}else{
				return ['code'=>-1,'msg'=>$output['errorMsg'],'data'=>$code];
			}
		}else{
				return ['code'=>-1,'msg'=>$result,'data'=>$code];;
		}

	}
}