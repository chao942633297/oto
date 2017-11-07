<?php
namespace app\home\controller;

use think\Controller;
use app\admin\model\UsersModel;
use service\Wechat as Wechats;

/**
* 
*/
class Wechat extends Controller
{

	public function checkToken()
	{
		Wechats::check();
	}

	#微信监听时间
	public function check()
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
	
	#判断浏览器类型
	public function BrowserType()
	{
		$pid = input('param.pid');
		if (is_weixin()) {
			$qrcode = UsersModel::where('id',$pid)->value('qrcode');
			
			#判断上级用户是否生成过ticket
			if (empty($qrcode)) {
				#获取ticket
				$qrcode = preg_replace('!https:\/\/mp\.weixin\.qq\.com\/cgi-bin\/showqrcode\?ticket=!','',Wechats::get_Qrcode($pid));
				#存上级用户的ticket
				UsersModel::where('id',$pid)->update(['qrcode'=>$qrcode]);
			}

            # 返回二维码链接
			$this->assign('ticket',$qrcode);
			# 获取jsapi
			$jsapi_config = Wechats::get_jsapi_config(['onMenuShareTimeline','onMenuShareAppMessage'],false,false);
			$this -> assign('jsapi_config',$jsapi_config);
			$this -> assign('pid',$pid);
			return $this->fetch('followpublic');			

		}else{
			header('location:http://h.runjiaby.com/register.html?param=&pid='.$pid);
		}
	}  

    #创建微信导航栏
    public function createmenu()
    {
    		$data = [
				['name'=>'进入商城','event'=>'view','val'=>'http://rj.runjiaby.com/home/wechat/wechatLogin'],
			];

			dump(Wechats::menu_create($data));
    }

    #微信点击登录
    public function wechatLogin()
    {	
    	#获取用户信息
        $userinfo = Wechats::get_user_info('http://rj.runjiaby.com/home/wechat/wechatLogin');

        $user = UsersModel::where('openid',$userinfo['openid'])->find();
        if (!empty($user)) {

        	#判断是否绑定手机号
        	if (empty($user->phone)) {
        		session('openid',$userinfo['openid']);
        		session('nickname',$userinfo['nickname']);
        		session('headimg',$userinfo['headimgurl']);

	        	#带着自己的id去注册
	        	if ($user->pid) {
	        		$url = 'http://h.runjiaby.com/register.html?param='.$user->id.'&pid='.$user->pid;
	        		header("location:$url");
	        	}else{
	        		$url = 'http://h.runjiaby.com/register.html?param='.$user->id.'&pid=';
	        		header("location:$url");
	        	}

        	}else{
        		session('uid',$user->id);
        		#param 为微信授权登陆 .前台添加session
        		header('location:http://h.runjiaby.com?param=1');
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
			
			// $users = UsersModel::where(['id'=>$newid]) -> find();
			// if($users){
			session('uid',$newid);
				// if ($users['phone'] !='') {
				// 	session('phone',$users->phone);
				// 	header('location:http://h.runjiaby.com?param=1');
				// }else{
			$url = 'http://rj.runjiaby.com/home/wechat/wechatLogin';
			header("location:$url");
				// }
			// }        	
        }


    }
}