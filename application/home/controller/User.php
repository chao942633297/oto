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

	#根据unique获取用户信息
	public function getUserPhoneByUnique()
	{
		$user = UsersModel::where('id',input('param.unique'))->value('phone');
		return json(['status'=>200,'msg'=>'ok','data'=>$user]);
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
		#获取当前用户
		if (empty($user)) {
			return json(['status'=>2000,'msg'=>'请登录']);
		}
		// $param = md5($user->phone);
		$param = $user->unique;
		qrcode($param);
		#取出生成的二维码
		$data = [];
		$data['path'] = ADMIN_URL.'/uploads/qrcode/'.$param.'.png';
		$data['username'] = $user->nickname;
		$data['headimg'] = $user->headimg;
		$data['unique'] = $param;
		if (IS_WECHAT) {
			#设置session shareUrl
			session('shareUrl',WEB_URL.'/code.html?unique='.$param);
			# 获取jsapi
			$jsapi_config = Wechats::get_jsapi_config(['onMenuShareTimeline','onMenuShareAppMessage'],false,false);
			# 分配JSapi配置
			$data['jsapi_config'] = $jsapi_config;
		}
		return json(['status'=>200,'msg'=>'成功生成二维码','data'=>$data]);
	}

	#获取编辑资料信息
	public function userEditInfo()
	{
		$user = UsersModel::where('id',$this->userId)->field('account,nickname,sex,headimg,wechat_qrcode')->find();
		return json(['status'=>200,'msg'=>'请求成功','data'=>$user]);
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

	#我的伙伴
	public function myPartner()
	{
		$data  = [];
		$count = UsersModel::where('pid',$this->userId)->count();	//伙伴总人数
		if (!$count) {
			$data['count'] = 0;
			$data['ordinary'] = [];
			$data['vip'] = [];
		}else{
			$ptuser  = UsersModel::where('pid',$this->userId)->where('type',1)->field('headimg,id,nickname,created_at,phone')->select();
			$vipuser  = UsersModel::where('pid',$this->userId)->where('type',2)->field('headimg,id,nickname,created_at,phone')->select();
			$data['count'] = $count;
			$data['ordinary'] = $ptuser;
			$data['vip'] = $vipuser;
		}

		return json(['status'=>200,'msg'=>'请求成功','data'=>$data]);
	}


	#用户名片
	public function userCard()
	{
		$id = input('param.id');
		$user = UsersModel::where('id',$id)->field('headimg,nickname,account,sex,phone,wechat_qrcode')->find();
		return json(['status'=>200,'msg'=>'请求成功','data'=>$user]);
	}

	#店铺收款二维码
	public function receivablesQrcode()
	{	
		$user = UsersModel::where('id',$this->userId)->find();
		#获取当前用户
		if (empty($user)) {
			return json(['status'=>2000,'msg'=>'请登录']);
		}
		ShopQrcode($user->unique);
		#取出生成的二维码
		$data = [];
		$data['path'] = ADMIN_URL.'/uploads/receivables/'.$user->unique.'.png';
		return json(['status'=>200,'msg'=>'请求成功','data'=>$data]);
	}

	#支付积分
	public function payScoreByShop()
	{
		$param = input('param.');

		if (empty($param)) {
			return json(['status'=>-1,'msg'=>'请传参数']);
		}
		$pay_password = UsersModel::where('id',$this->userId)->value('pay_password');
		// if ( md5($param['pay_password']) != $pay_password ) {
		// 	return json(['status'=>-1,'msg'=>'支付密码错误']);
		// }
		Db::startTrans();
		#查询扫码用户的可用积分
		$scores = Db::table('integral')->where(['uid'=>$this->userId,'type'=>1])->where('is_add',1)->sum('value');
		$score = Db::table('integral')->where(['uid'=>$this->userId,'type'=>1])->where('is_add',2)->sum('value');
		$trueScore = sprintf("%.2f",$scores - $score);	

		if ( abs($param['score']) >$trueScore ) {
			return json(['status'=>-1,'msg'=>'积分不够']);
		}
		$shopUser = UsersModel::where('unique',$param['unique'])->find();
		if (empty($shopUser)) {
			return json(['status'=>-1,'msg'=>'信息错误,请重新扫码支付']);
		}
		if ($shopUser['is_union'] != 2) {
			return json(['status'=>-1,'msg'=>'信息错误,请重新扫码支付']);
		}

		$data = [];		//店家加积分
		$data['uid'] = $shopUser['id'];
		$data['value'] = abs($param['score']);
		$data['is_add'] = 1;
		$data['message'] = '线下扫码';
		$data['type'] = 2;//店家积分,可提现
		$data['source'] = $this->userId;
		$data['img'] = $param['img'];
		$data['created_at'] = time();

		$datas= [];		//用户减积分
		$datas['uid'] =  $this->userId;
		$datas['value'] = abs($param['score']);
		$datas['is_add'] = 2;
		$datas['message'] = '线下消费购物';
		$datas['type'] = 1;//消费积分
		$datas['source'] = $this->userId;
		$datas['created_at'] = time();
		try {
			$res = Db::table('integral')->insert($data);
			$res1 = Db::table('integral')->insert($datas);
			if ($res && $res1) {
				Db::commit();
				return json(['status'=>200,'msg'=>'支付成功']);
			}
				return json(['status'=>-1,'msg'=>'支付失败']);
		} catch (Exception $e) {
				Db::rollback();
				return json(['status'=>-1,'msg'=>'支付失败']);
		}	
	}





}