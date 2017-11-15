<?php
namespace app\home\controller;

use think\Controller;
use app\admin\model\UsersModel;
use service\Wechat as Wechats;
use service\ChuanglanSmsApi;

/**
* 
*/
class Wechat extends Controller
{
	const WEB_URL = "http://www.oto178.com";
	const ADMIN_URL = "http://admin.oto178.com";
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
			// $data['sex'] = $userinfo['sex'];
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

	#授权登录
	public function BrowserType()
	{
		$res = ChuanglanSmsApi::sendCode('13103810741');
		dump($res);
		die('1');
		null !== input('param.unique') ? $unique = input('param.unique') : $unique = '';
		
		#查询上级
		if (empty($unique)) {
			$father = [];
		}else{
			$father = UsersModel::where('unique',$unique)->find(); 
		}
		
		if (IS_WECHAT) {
			$userinfo = Wechats::get_user_info(self::ADMIN_URL."/home/wechat/BrowserType?unique=".$unique);
			$res = UsersModel::where('openid',$userinfo['openid'])->find();
			if (!empty($res)) {
				session('uid',$res['id']);
				session('phone',$res['phone']);
				session('unique',$res['unique']);
				redirect('http://www.oto178.com/');
			}else{
				$data = [];

				$data['pid'] = $father['id'];
				# 用户唯一标识
				$data['openid'] = $userinfo['openid'];
				# 性别 1=男 2=女性 0=未设置
				// $data['sex'] = $userinfo['sex'];
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
			$url = self::WEB_URL."register.html?unique=".$unique;
			header("location:$url");
		}
	} 
    #创建微信导航栏
    public function createmenu()
    {
    		$data = [
				['name'=>'进入商城','event'=>'view','val'=>self::ADMIN_URL.'/home/wechat/wechatLogin?type=1'],
				['name'=>'个人中心','event'=>'view','val'=>self::ADMIN_URL.'/home/wechat/wechatLogin?type=2'],
			];

			dump(Wechats::menu_create($data));
    }

    #微信点击登录
    public function wechatLogin()
    {	
    	null !== input('param.type') ? $type = input('param.type') : $type = 1 ;
    	echo "<h1 style='text-align:center;margin:100px auto;color:red'>维护中</h1>";
    	die();
    	#获取用户信息
        $userinfo = Wechats::get_user_info(self::ADMIN_URL.'/home/wechat/wechatLogin?type='.$type);

        $user = UsersModel::where('openid',$userinfo['openid'])->find();
        #判断是否数据库已存在微信信息
        if (!empty($user)) {

        	#判断是否绑定手机号
        	if (empty($user->phone)) {
        		session('openid',$userinfo['openid']);
        		session('nickname',$userinfo['nickname']);
        		session('headimg',$userinfo['headimgurl']);

	        	#带着上级的标识去绑定手机号,unique为上级的标识
	        	if ($user->pid) {
	        		$father = UsersModel::where('id',$user->pid)->find();

	        		$url = self::WEB_URL.'/bind.html?unique='.$father->unique.'&phone='.$father->phone;

	        		header("location:$url");
	        	}else{
	        		#自己绑定,无上级 unique 为自己的标识
	        		$url = self::WEB_URL.'/register.html?unique='.$user->unique;
	        		header("location:$url");
	        	}

        	}else{
				session('uid',$res['id']);
				session('phone',$res['phone']);
				session('unique',$res['unique']);
        		#微信授权登陆
        		header("location:self::WEB_URL");
        	}

        }else{
			# 获取用户信息
			# 用户数组
			$data = [];
			# 用户唯一标识
			$data['openid'] = $userinfo['openid'];
			$data['nickname'] = $userinfo['nickname'];
			$data['headimg'] = $userinfo['headimgurl'];
			$data['created_at'] = time();

			#防止微信昵称重复
			$ni = UsersModel::where(['nickname'=>$data['nickname']])->find();
			if (!empty($ni)) {
				$data['nickname'] = $data['nickname'].rand(1,10000);
			}

			#插入微信信息
			$newid = UsersModel::insertGetId($data);
			$url = self::ADMIN_URL.'/home/wechat/wechatLogin?type='.$type;
			header("location:$url");
        }
    }


    #绑定手机号接口
    public function bindPhone()
    {
    	$param = input('param.');

    }
}