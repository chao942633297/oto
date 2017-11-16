<?php
namespace app\home\controller;

use app\admin\model\UsersModel;

use think\Controller;
use think\Db;
use service\Wechat as Wechats;
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

	#根据id获取用户信息
	public function getUserInfoById()
	{
		$user = UsersModel::where('id',$this->userId)->find();
		return json(['status'=>1,'msg'=>'ok','data'=>$user]);
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
		$data['path'] = ADMIN_URL.'/uploads/qrcode/'.$param.'.png';
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


	#编辑用户信息
	public function doEdit()
	{
		$param = input('param.');

		if ($param['sex'] == 0) {
			return json(['status'=>-1,'msg'=>'选择性别']);
		}
		$model = new UsersModel();
		$result = $model->allowField(true)->save($param);
		if ($result) {
			return json(['status'=>1,'msg'=>'编辑成功']);
		}
			return json(['status'=>-1,'msg'=>'提交失败']);
	}


}