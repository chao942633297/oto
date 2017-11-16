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
	
	public function _initialize()
	{
		$this->userId = session('uid');
		parent::_initialize();

	}

	#根据id获取用户信息
	public function getUserInfoById()
	{
		$user = UsersModel::where('id',$this->userId)->field('headimg,nickname,type')->find();
		return json(['status'=>200,'msg'=>'ok','data'=>$user]);
	}

	#点击设置进去
	public function userSetting()
	{
		$user = UsersModel::where('id',$this->userId)->field('headimg,nickname,account,bank')->find();
		$user['bank'] = unserialize($user['bank']);
		return json(['status'=>200,'msg'=>'ok','data'=>$user]);
	}

	#绑定银行卡
	public function bindBank()
	{

		$param = input('param.');
		if (empty($param)) {
			return json(['status'=>-1,'msg'=>'请传参数']);
		}
		if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $param['card'])) {
			return json(['status'=>-1,'msg'=>'身份证格式错误']);
		}
		if(!preg_match('/^([1-9]{1})(\d{14}|\d{18})$/', $param['bank_id'])){
			return json(['status'=>-1,'msg'=>'银行卡号格式错误']);
		} 
		$data = [];
		$data['card'] = $param['card'];		//身份证
		$data['bank_name'] = $param['bank_name'];		//开户行
		$data['bank_person'] = $param['bank_person'];		//开户人
		$data['bank_id'] = $param['bank_id'];		//银行卡号
		$data['bank_zname'] = $param['bank_zname'];		//开户支行

		$result = UsersModel::where('id',$this->userId)->update(['card'=>$param['card'],'bank'=>serialize($data)]);
		if ($result) {
			return json(['status'=>200,'msg'=>'绑定成功']);
		}else{
			return json(['status'=>-1,'msg'=>'绑定失败']);
		}

	}


	#用户二维码页面
	public function qrcode()
	{
		$user = UsersModel::where('id',$this->userId)->find();
#		获取当前用户
		if (empty($user)) {
			return jsonp(['status'=>2000,'msg'=>'请登录']);
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
		return jsonp(['status'=>200,'msg'=>'成功生成二维码','data'=>$data]);
	}

	#获取编辑资料信息
	public function userEditInfo()
	{
		$user = UsersModel::where('id',$this->userId)->field('account,nickname,sex,headimg,wechat_qrcode')->find();
		return jsonp(['status'=>200,'msg'=>'请求成功','data'=>$user]);
	}

	#编辑用户信息
	public function doEditInfo()
	{
		$param = input('param.');

		if ($param['sex'] == 0) {
			return json(['status'=>-1,'msg'=>'选择性别']);
		}
		$model = new UsersModel();
		$result = $model->allowField(true)->save($param);
		if ($result) {
			return json(['status'=>200,'msg'=>'编辑成功']);
		}
			return json(['status'=>-1,'msg'=>'提交失败']);
	}


	#申请线下联盟商
	public function applyLineUnionMerchant()
	{

		$param = input('param.');
		$param['uid'] = $this->userId;
		$param['created_at'] = time();
		if (empty($param['license'])) {
			return json(['status'=>-1,'msg'=>'请上传营业执照']);
		}
		if (empty($param['address_detail'])) {
			return json(['status'=>-1,'msg'=>'请上传营业执照']);
		}	

		$result = Db::table('union_apply')->insert($param);
		if ($result) {
			return json(['status'=>200,'msg'=>'提交成功']);
		}	
			return json(['status'=>-1,'msg'=>'提交失败']);
	}

	#申请线下联盟商--状态
	public function applying()
	{
		$apply = Db::table('union_apply')->where('uid',$this->userId)->where('is_del',0)->find();
		if (!empty($apply)) {
			return 	json(['status'=>200,'msg'=>'查询成功','data'=>$apply['status']]);
		}
	}

}