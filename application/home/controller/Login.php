<?php
namespace app\home\controller;

use app\admin\model\UsersModel;
use think\Controller;
use service\ChuanglanSmsApi;
use app\home\model\PhoneCode;
#登录
class Login extends Controller
{	

	#登录 -- 差发红包
	public function doLogin()
	{
		$param = input('param.');
		if (!preg_match("/^1[34578]\d{9}$/", $param['phone'])) {
			return json(['status'=>-1,'msg'=>'手机号格式错误']);	
		}		
		$user = UsersModel::where('phone',$param['phone'])->find();
		if (empty($user)) {
			return json(['status'=>-1,'msg'=>'此账号不存在']);
		}
		if ($user['password'] != md5($param['password'])) {
			return json(['status'=>-1,'msg'=>'账号或者密码错误']);
		}
		session('uid',$user['id']);
		return json(['status'=>200,'msg'=>'ok']);
	}


	#注册
	public function doRegister()
	{
		$data = [];
		$param = input('param.');
		$model = new UsersModel();
		if (!preg_match("/^1[34578]\d{9}$/", $param['phone'])) {
			return json(['status'=>-1,'msg'=>'手机号格式错误']);	
		}	
		#查询上级
		$father = $model->where('phone',$param['fatherPhone'])->find();
		
		$own = $model->where('phone',$param['phone'])->find();
		if (!empty($own)) {
			return json(['status'=>-1,'msg'=>'此用户已存在']);
		}
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
		$data['phone'] = $param['phone'];
		$data['unique'] = md5($param['phone']);
		$data['headimg'] = $param['headimg'];
		$data['nickname'] = "用户".$param['phone'];
		$data['password'] = md5($param['password']);
		$data['created_at'] = time();
		#注册用户插入表中
		$result = $model->allowField(true)->save($data);
		if ($result== 1) {
			return json(['status'=>1,'msg'=>'注册成功']);
		}

		return json(['status'=>-1,'msg'=>'注册失败']);

	}

	#忘记密码/修改登录密码
	public function forgetPwd()
	{
		$param = input('param.');
		$model = new UsersModel();
		if (!preg_match("/^1[34578]\d{9}$/", $param['phone'])) {
			return json(['status'=>-1,'msg'=>'手机号格式错误']);	
		}	
		$own = $model->where('phone',$param['phone'])->find();
		if (empty($own)) {
			return json(['status'=>-1,'msg'=>'此用户不存在']);
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

		$data = [];
		$data['id'] = $own['id'];
		$data['password'] = md5($param['password']);
		$data['updated_at'] = time();
		#修改用户密码插入表中
		$result = $model->allowField(true)->save($data,['id'=>$data['id']]);
		if ($result== 1) {
			return json(['status'=>1,'msg'=>'修改成功']);
		}
		return json(['status'=>-1,'msg'=>'修改失败']);
	}
	#修改支付密码
	public function forgetPayPwd()
	{
		$param = input('param.');
		$model = new UsersModel();
		if (!preg_match("/^1[34578]\d{9}$/", $param['phone'])) {
			return json(['status'=>-1,'msg'=>'手机号格式错误']);	
		}	
		$own = $model->where('phone',$param['phone'])->find();
		if (empty($own)) {
			return json(['status'=>-1,'msg'=>'此用户不存在']);
		}

		if(!preg_match('/^\d*$/',$param['password'])){
			return json(['status'=>-1,'msg'=>'支付密码格式不对']);
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

		$data = [];
		$data['id'] = $own['id'];
		$data['pay_password'] = md5($param['password']);
		$data['updated_at'] = time();
		#修改用户支付密码插入表中
		$result = $model->allowField(true)->save($data,['id'=>$data['id']]);
		if ($result== 1) {
			return json(['status'=>1,'msg'=>'修改成功']);
		}
		return json(['status'=>-1,'msg'=>'修改失败']);
	}

	#获取用户信息
	public function getUserInfo($unique)
	{
		$user = UsersModel::where('unique',$unique)->find();
		if (empty($user)) {
			return json(['status'=>-1,'msg'=>'此用户不存在']);
		}
		return json(['status'=>200,'msg'=>'ok','data'=>$user]);
	}

	#发短信
	public function sendCode()
	{
		$param = input('param.');
		if (!isset($param['phone'])) {
			return json(['status'=>-1,'msg'=>'请传参数']);	
		}
		$user = PhoneCode::where('phone',$param['phone'])->order('created_at','desc')->find();
		if (!empty($user)) {
			#判断发短信是否在十分钟之内
			if ( ( (int)time() - (int)$user->created_at )  < 6000 ) {
				return json(['status'=>-1,'msg'=>'上次验证码还未失效']);	
			}
		}

		if (!preg_match("/^1[34578]\d{9}$/", $param['phone'])) {
			return json(['status'=>-1,'msg'=>'手机号格式错误']);	
		}

		// $res = ChuanglanSmsApi::sendCode($param['phone']);	//发短信接口
		$res = ['code'=>1,'data'=>'1111'];
		#判断短信平台发短信是否成功
		if ($res['code'] == 1) {
			$PhoneCode = new PhoneCode();
			$PhoneCode->insert(['phone'=>$param['phone'],'code'=>$res['data'],'created_at'=>time()]);
			return json(['status'=>200,'msg'=>'短信发送成功','data'=>$res['data']]);
		}else{
			return json(['status'=>-1,'msg'=>'短信服务出错']);	
		}
	}


	#退出
	public function logOut()
	{
		session('uid',null);
		return json(['status'=>200,'msg'=>'退出成功']);
	}


}