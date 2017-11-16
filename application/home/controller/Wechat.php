<?php
namespace app\home\controller;

use think\Controller;
use app\admin\model\UsersModel;
use app\home\model\PhoneCode;
use service\Wechat as Wechats;
use service\ChuanglanSmsApi;
use think\Db;
/**
* 
*/
class Wechat extends Controller
{

	// public function checkToken()
	// {
		
	// 	//ototoken178
	// 	//94aAmItmgSULLpOHqtstJEyykjmHuciyMpzDs4gX6QE
	// 	Wechats::checkToken('ototoken178');
	// }

	#微信监听时间
	public function checkToken()
	{   

    	#监听关注公众号事件
    	Wechats::addEvent('subscribe',function($result){
    	echo "<h1 style='text-align:center;margin:100px auto;color:red'>升级中</h1>";
    	die();
    		#判断是否存在此用户
    		if ( UsersModel::where('openid',$result['FromUserName'])-> find() ) {
    			return true;
    		}
			# 自动回复
			$userinfo = Wechats::get_openid_user_info($result['FromUserName']);
			# 用户数组
			$data = [];
			# 判断是否存在票据
			if($result['Ticket']!=''){
				# 获取上级信息
				$fatherInfo = UsersModel::where('qrcode',$result['Ticket'])-> find();
				if( $fatherInfo ){
					// 设置上级id
					$data['pid'] = $fatherInfo['id'];
				}
			}

			# 用户唯一标识
			$data['openid'] = $userinfo['openid'];
			# 性别 1=男 2=女性 0=未设置
			$data['sex'] = $userinfo['sex'];
			# 城市
			// $data['city'] = $userinfo['city'];
			# 省份
			// $data['province'] = $userinfo['province'];
			# 用户昵称
			$data['nickname'] = $userinfo['nickname'];

			$data['headimg'] = $userinfo['headimgurl'];
			# 国籍
			// $data['country'] = $userinfo['country'];

			$data['created_at'] = time();

			$res = UsersModel::insert($data);

    	});#监听关注公众号事件---------结束

	}
	
	#判断浏览器类型----生成关注公众号二维码
	// public function BrowserType()
	// {
	// 	$pid = input('param.unique');
	// 	if (IS_WECHAT) {
	// 		$qrcode = UsersModel::where('unique',$pid)->value('qrcode');
			
	// 		#判断上级用户是否生成过ticket
	// 		if (empty($qrcode)) {
	// 			#获取ticket
	// 			$qrcode = preg_replace('!https:\/\/mp\.weixin\.qq\.com\/cgi-bin\/showqrcode\?ticket=!','',Wechats::get_Qrcode($pid));
	// 			#存上级用户的ticket
	// 			UsersModel::where('id',$pid)->update(['qrcode'=>$qrcode]);
	// 		}

 //            # 返回二维码链接
	// 		$this->assign('ticket',$qrcode);
	// 		# 获取jsapi
	// 		$jsapi_config = Wechats::get_jsapi_config(['onMenuShareTimeline','onMenuShareAppMessage'],false,false);
	// 		$this -> assign('jsapi_config',$jsapi_config);
	// 		$this -> assign('pid',$pid);
	// 		return $this->fetch('followpublic');			

	// 	}else{
	// 		$url = self::WEB_URL;
	// 		header("location:$url");
	// 	}
	// }  

	#判断浏览器类型 -- 授权登录
	public function BrowserType()
	{
    	echo "<h1 style='text-align:center;margin:100px auto;color:red'>升级中</h1>";
    	die();
		null !== input('param.unique') ? $unique = input('param.unique') : $unique = '';
		
		#查询上级
		if (empty($unique)) {
			$father = [];
		}else{
			$father = UsersModel::where('unique',$unique)->find(); 
		}
		
		if (IS_WECHAT) {
			$userinfo = Wechats::get_user_info(ADMIN_URL."/home/wechat/BrowserType?unique=".$unique);
			$res = UsersModel::where('openid',$userinfo['openid'])->find();
			if (!empty($res)) {
				session('uid',$res['id']);
				session('phone',$res['phone']);
				session('unique',$res['unique']);
				header("location:WEB_URL");
			}else{
				$data = [];

				$data['pid'] = $father['id'];
				# 用户唯一标识
				$data['openid'] = $userinfo['openid'];
				# 性别 1=男 2=女性 0=未设置
				$data['sex'] = $userinfo['sex'];
				# 城市
				// $data['city'] = $userinfo['city'];
				# 省份
				// $data['province'] = $userinfo['province'];
				# 用户昵称
				$data['nickname'] = $userinfo['nickname'];

				$data['headimg'] = $userinfo['headimgurl'];
				# 国籍
				// $data['country'] = $userinfo['country'];
				$data['created_at'] = time();

				$res = UsersModel::insert($data);				
			}
		}else{
			$url = WEB_URL."register.html?unique=".$unique;
			header("location:$url");
		}
	} 
    #创建微信导航栏
    public function createmenu()
    {
    		$data = [
				['name'=>'进入商城','event'=>'view','val'=>ADMIN_URL.'/home/wechat/wechatLogin?type=1'],
				['name'=>'个人中心','event'=>'view','val'=>ADMIN_URL.'/home/wechat/wechatLogin?type=2'],
			];

			dump(Wechats::menu_create($data));
    }

    #微信点击登录
    public function wechatLogin()
    {	
    	null !== input('param.type') ? $type = input('param.type') : $type = 1;
    	echo "<h1 style='text-align:center;margin:100px auto;color:red'>升级中</h1>";
    	die();
    	#获取用户信息
        $userinfo = Wechats::get_user_info(ADMIN_URL.'/home/wechat/wechatLogin?type='.$type);

        $user = UsersModel::where('openid',$userinfo['openid'])->find();
        #判断是否数据库已存在微信信息
        if (!empty($user)) {

        	#判断是否绑定手机号
        	if (empty($user->phone)) {
        		session('openid',$userinfo['openid']);
        		session('nickname',$userinfo['nickname']);
        		session('headimg',$userinfo['headimgurl']);
        		session('sex',$userinfo['sex']);

	        	#带着上级的标识去绑定手机号,unique为上级的标识

	        	$url = WEB_URL.'/bind.html?type='.$type;
	        	if ($user->pid) {
	        		$father = UsersModel::where('id',$user->pid)->find();

	        		$url .= '&unique='.$father->unique;
	        		header("location:$url");
	        	}
	        	$url .= '&unique=';
	        	header("location:$url");
        	}else{
				session('uid',$res['id']);
				session('phone',$res['phone']);
				session('unique',$res['unique']);
        		#微信授权登陆
        		header("location:WEB_URL");
        	}

        }else{
			# 获取用户信息
			# 用户数组
			$data = [];
			# 用户唯一标识
			$data['openid'] = $userinfo['openid'];
			$data['nickname'] = $userinfo['nickname'];
			$data['headimg'] = $userinfo['headimgurl'];
			$data['sex'] = $userinfo['sex'];
			$data['created_at'] = time();

			#防止微信昵称重复
			$ni = UsersModel::where(['nickname'=>$data['nickname']])->find();
			if (!empty($ni)) {
				$data['nickname'] = $data['nickname'].rand(1,10000);
			}

			#插入微信信息
			$newid = UsersModel::insertGetId($data);
			$url = ADMIN_URL.'/home/wechat/wechatLogin?type='.$type;
			header("location:$url");
        }
    }

    #绑定手机号接口
    public function bindPhone($param)
    {
		$data = [];
		// $param = input('param.');
		$param = $param;
		$model = new UsersModel();
		if (!preg_match("/^1[34578]\d{9}$/", $param['phone'])) {
			return json(['status'=>-1,'msg'=>'手机号格式错误']);	
		}	
		#查询上级
		$father = $model->where('phone',$param['fatherPhone'])->find();
		#判断上级是否存在
		if (empty($father)) {

			return json(['status'=>-1,'msg'=>'上级用户不存在']);
		}
		#判断验证码和手机号
		$check = PhoneCode::checkCode($param['phone'],$param['code']);
		if (!$check) {

			return json(['status'=>-1,'msg'=>'验证码错误']);
		}


		#再次确认密码
		if ($param['password'] != $param['repassword']) {

			return json(['status'=>-1,'msg'=>'两次输入密码不一致']);
		}

		$data['pid'] = $father['id'];
		$data['sex'] = session('sex');
		$data['unique'] = md5($param['phone']);
		$data['openid'] = session('openid');
		$data['headimg'] = session('headimg');
		$data['nickname'] = session('nickname');
		$data['password'] = md5($param['password']);
		$data['updated_at'] = time();
		#注册用户插入表中
		Db::startTrans();
		try {
			$result1 = UsersModel::where('openid',session('openid'))->delete();
			$result = $model->allowField(true)->save($data,['phone'=>$param['phone']]);
			if ($result && $result1) {
				Db::commit();
				return ['status'=>200,'msg'=>'绑定成功'];
			}
		} catch (Exception $e) {
			Db::rollback();

			file_put_contents("bindWechat.txt", $e->getMessage().PHP_EOL,FILE_APPEND);
		}
		return json(['status'=>-1,'msg'=>'绑定失败']);
    }


	#绑定微信发短信
	public function sendBindPhoneCode()
	{
		$param = input('param.');
		if (!isset($param['phone'])) {
			return json(['status'=>-1,'msg'=>'请传参数']);	
		}
		if (!preg_match("/^1[34578]\d{9}$/", $param['phone'])) {
			return json(['status'=>-1,'msg'=>'手机号格式错误']);	
		}
		$own = UsersModel::where('phone',$param['phone'])->find();
		if (!empty($own) && !empty($own['openid'])) {
			return json(['status'=>-1,'msg'=>'此手机号已经绑定过微信']);
		}

		$user = PhoneCode::where('phone',$param['phone'])->order('created_at','desc')->find();
		if (!empty($user)) {
			#判断发短信是否在十分钟之内
			if ( ( (int)time() - (int)$user->created_at )  < 6000 ) {
				return json(['status'=>-1,'msg'=>'上次验证码还未失效']);	
			}
		}
		// $res = ChuanglanSmsApi::sendCode($param['phone']);	//发短信接口
		$res = ['code'=>200,'data'=>'1111'];
		#判断短信平台发短信是否成功
		if ($res['code'] == 1) {
			$PhoneCode = new PhoneCode();
			$PhoneCode->insert(['phone'=>$param['phone'],'code'=>$res['data'],'created_at'=>time()]);
			return json(['status'=>200,'msg'=>'短信发送成功','data'=>$res['data']]);
		}else{
			return json(['status'=>-1,'msg'=>'短信服务出错']);	
		}
	}
}