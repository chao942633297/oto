<?php
namespace app\home\controller;

use app\admin\model\UsersModel;

use think\Controller;
use think\Db;
#用户管理
class User extends Base
{	
	#用户ID
	protected $userId;
	
	public function __construct()
	{
		parent::__construct();
		$this->userId = session('uid');
	}


	#用户二维码页面
	public function qrcode()
	{
		$user = UsersModel::where('id',$this->userId)->find();
#		获取当前用户
		if (empty($user)) {
			return jsonp(['status'=>2000,'message'=>'请登录']);
		}
		$param = md5($user->phone);
		qrcode($param);
		#取出生成的二维码
		$data = [];
		$data['path'] = WAB_NAME.'/uploads/qrcode/'.$param.'.png';
		$data['username'] = $user->nickname;
		$data['headimg'] = $user->headimg;
		$data['unique'] = $param;
		if (IS_WECHAT) {
			#设置session shareUrl
			session('shareUrl','http://www.oto178.com/code.html?unique='.$param);
			# 获取jsapi
			$jsapi_config = Wechats::get_jsapi_config(['onMenuShareTimeline','onMenuShareAppMessage'],false,false);
			# 分配JSapi配置
			$data['jsapi_config'] = $jsapi_config;
		}
		return jsonp(['status'=>200,'message'=>'成功生成二维码','data'=>$data]);
	}


}