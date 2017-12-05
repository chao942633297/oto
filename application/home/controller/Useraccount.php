<?php
namespace app\home\controller;

use app\admin\model\UsersModel;
use app\admin\model\UserMoneyLog;
use app\admin\model\Integral;
use app\admin\model\Config;
use app\admin\model\Withdrawals;
use think\Db;

#用户账户
class Useraccount extends Base
{	
	protected $userId;

	public function _initialize()
	{
		parent::_initialize();
		$this->userId = session('uid');
	}

	#我的账户
	public function myAccount()
	{
		// $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		// $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$data = [];
		$type = UsersModel::where('id',$this->userId)->value('type');
		if ($type == 1) {
			$data['today'] = 0;
		}elseif ($type == 2) {
			$data['today'] = mt_rand(100,500);
		}
		// $today = UserMoneyLog::where('uid',$this->userId)->where('is_add',1)->whereBetween('created_at',[$beginToday,$endToday])->sum('money');
		$myLP = UserMoneyLog::getBalance($this->userId,1);
		$myNLP = UserMoneyLog::getBalance($this->userId,2);
		// $data['today'] = $today;
		$data['myLP'] = $myLP;
		$data['myNLP'] = $myNLP;
		return json(['status'=>200,'msg'=>'请求成功','data'=>$data]);
	}

	#获取用户可使用的粮票
	public function useLP()
	{
		$money = UserMoneyLog::getBalance($this->userId,1);
		return json(['status'=>200,'msg'=>'请求成功','data'=>$money]);
	}

	#粮票提现
	public function  cashLP()
	{	
		$param = input('param.');
		if (null === input('param.money')) {
			return json(['status'=>-1,'msg'=>'请传参数']);
		}
		$money = UserMoneyLog::getBalance($this->userId,1);
		if ( !is_numeric($param['money']) || $param['money'] % 100 != 0 || $param['money'] < Config::getConfig('cash_little_money') ) {
			return json(['status'=>-1,'msg'=>'提现金额有误']);
		}
		if ($money < $param['money']) {
			return json(['status'=>-1,'msg'=>'粮票不足']);
		}
		$bank = UsersModel::where('id',$this->userId)->find();
		if (empty($bank['bank'])) {
			return json(['status'=>-1,'msg'=>'请先绑定银行卡']);
		}

		// if (md5($param['password']) != $bank['pay_password']) {
		// 	return json(['status'=>-1,'msg'=>'支付密码错误']);
		// }
		$Withdrawals = [];		//提现表数组 --Withdrawals
		$Withdrawals['order_num'] = order_sn();
		$Withdrawals['uid'] = $this->userId;
		$Withdrawals['money'] = abs($param['money']);
		$Withdrawals['real_money'] = abs($param['money']) * (Config::getConfig('cash_money') / 100 );
		$Withdrawals['type'] = 2;
		$Withdrawals['created_at'] = time();

		$data = [];		//减少余额 --user_money_log
		$data['uid'] = $this->userId;
		$data['money'] = abs($param['money']);
		$data['is_add'] = 2;
		$data['status'] = 1;
		$data['message'] = '提现';
		$data['type'] = 6;
		$data['created_at'] = time();
		
		$datas= [];		//用户加积分 -- integral
		$datas['uid'] =  $this->userId;
		$datas['value'] = abs($param['money']) * (Config::getConfig('cash_score') / 100 );
		$datas['is_add'] = 1;
		$datas['message'] = '粮票提现';
		$datas['type'] = 1;//消费积分
		$datas['source'] = 1;
		$datas['created_at'] = time();

		Db::startTrans();
		try {
			$orderId = Db::table('withdrawals')->insertGetId($Withdrawals);
			$data['withdrawals_id'] = $orderId;
			$datas['withdrawals_id']= $orderId;
			$res  = Db::table('user_money_log')->insert($data);
			$res1 = Db::table('integral')->insert($datas);
			if ($res && $res1) {
				Db::commit();
				return json(['status'=>200,'msg'=>'提现成功']);
			}
			return json(['status'=>-1,'msg'=>'提现失败']);
		} catch (Exception $e) {
			Db::rollback();
			return json(['status'=>-1,'msg'=>'提现失败']);
		}
	}


	#提现记录
	public function cashLog()
	{
		$log = Withdrawals::where('uid',$this->userId)->select();
		if (!empty($log)) {
			foreach ($log as $k => $v) {
				$log[$k]['type'] = Withdrawals::TYPE[$v->type];
				$log[$k]['status'] = Withdrawals::STATUS[$v->status];
				$log[$k]['created_at'] = date('Y-m-d H:i:s',$v->created_at);
			}
		}else{
			$log = [];
		}
		return json(['status'=>200,'msg'=>'请求成功','data'=>$log]);
	}

	#分享奖
	public function sharePrize()
	{
		$data = [];
		$money = UserMoneyLog::getTypeBalance($this->userId,1);
		$moneyList = UserMoneyLog::getTypeBalanceList($this->userId,1);
		$data['count'] = $money;
		if (empty($moneyList)) {
			$data['moneyList'] = [];
		}else{
			foreach ($moneyList as $k => $v) {
				$moneyList[$k]['username'] = $v->sources->nickname;
				$moneyList[$k]['headimg'] = $v->sources->headimg;
				$moneyList[$k]['phone'] = $v->sources->phone;
			}
			$data['moneyList'] = $moneyList;
		}
		return json(['status'=>200,'msg'=>'请求成功','data'=>$data]);
	}

	#感恩奖
	public function thanksgiving()
	{
		$data = [];
		$money = UserMoneyLog::getTypeBalance($this->userId,2);
		$moneyList = UserMoneyLog::getTypeBalanceList($this->userId,2);
		$data['count'] = $money;
		if (empty($moneyList)) {
			$data['moneyList'][] = [];
		}else{
			foreach ($moneyList as $k => $v) {
				if (empty($v->source)) {
					$moneyList[$k]['username'] ='';
					$moneyList[$k]['headimg'] = '';
					$moneyList[$k]['phone'] = 	'';			
				}else{
					$moneyList[$k]['username'] = $v->sources->nickname;
					$moneyList[$k]['headimg'] = $v->sources->headimg;
					$moneyList[$k]['phone'] = $v->sources->phone;
				}
			}
			$data['moneyList'] = $moneyList;
		}
		return json(['status'=>200,'msg'=>'请求成功','data'=>$data]);		
	}

	#共享奖
	public function partakePrize()
	{

	}

	#粮票收支详情
	public function paymentsDetail()
	{
		$data = [];
		$data['shouru'] = UserMoneyLog::where(['uid'=>$this->userId,'is_add'=>1])->select();
		$data['zhichu'] = UserMoneyLog::where(['uid'=>$this->userId,'is_add'=>2])->select();
		#收入
		if (empty($data['shouru'])) {
			$data['shouru'] = [];
		}else{
			foreach ($data['shouru'] as $k => $v) {
				$data['shouru'][$k]['type'] = UserMoneyLog::TYPE[$v->type];
				$data['shouru'][$k]['status'] = UserMoneyLog::STATUS[$v->status];
				$data['shouru'][$k]['created_at'] = date('Y-m-d H:i:s');
			}
		}
		#支出
		if (empty($data['zhichu'])) {
			$data['zhichu'] = [];
		}else{
			foreach ($data['zhichu'] as $k => $v) {
				$data['zhichu'][$k]['type'] = UserMoneyLog::TYPE[$v->type];
				$data['zhichu'][$k]['status'] = UserMoneyLog::STATUS[$v->status];
				$data['zhichu'][$k]['created_at'] = date('Y-m-d H:i:s');
			}
		}
		return json(['status'=>200,'msg'=>'请求成功','data'=>$data]);
	}


	#我的积分
	public function myIntegral()
	{
		$data = [];
		$count = Integral::getTypeBalance($this->userId,1);
		$shouru = Integral::getTypeBalanceList($this->userId,1,1);
		$zhichu = Integral::getTypeBalanceList($this->userId,1,2);
		$data['count'] = $count;
		if (empty($shouru)) {
			$data['shouru'] = [];
		}else{
			foreach ($shouru as $k => $v) {
				$shouru[$k]['type'] = Integral::TYPE[$v->type];
				$shouru[$k]['source'] = Integral::SOURCE[$v->source];
				$shouru[$k]['created_at'] = date('Y-m-d H:i:s',$v->created_at);
			}
			$data['shouru'] = $shouru;
		}
		if (empty($zhichu)) {
			$data['zhichu'] = [];
		}else{
			foreach ($zhichu as $k => $v) {
				$zhichu[$k]['type'] = Integral::TYPE[$v->type];
				$zhichu[$k]['source'] = Integral::SOURCE[$v->source];
				$zhichu[$k]['created_at'] = date('Y-m-d H:i:s',$v->created_at);
			}
			$data['zhichu'] = $zhichu;
		}
		return json(['status'=>200,'msg'=>'请求成功','data'=>$data]);
	}

	#我的收款积分
	public function myReceivablesIntegral()
	{
		$data = [];
		$count = Integral::getTypeBalance($this->userId,2);
		$shouru = Integral::getTypeBalanceList($this->userId,2,1);
		$zhichu = Integral::getTypeBalanceList($this->userId,2,2);
		$data['count'] = $count;
		if (empty($shouru)) {
			$data['shouru'] = [];
		}else{
			foreach ($shouru as $k => $v) {
				$shouru[$k]['type'] = Integral::TYPE[$v->type];
				$shouru[$k]['source'] = Integral::SOURCE[$v->source];
				$shouru[$k]['source'] = date('Y-m-d H:i:s',$v->created_at);
			}
			$data['shouru'] = $shouru;
		}
		if (empty($zhichu)) {
			$data['zhichu'] = [];
		}else{
			foreach ($zhichu as $k => $v) {
				$zhichu[$k]['type'] = Integral::TYPE[$v->type];
				$zhichu[$k]['source'] = Integral::SOURCE[$v->source];
				$zhichu[$k]['source'] = date('Y-m-d H:i:s',$v->created_at);
			}
			$data['zhichu'] = $zhichu;
		}
		return json(['status'=>200,'msg'=>'请求成功','data'=>$data]);
	}

	#兑换积分
	public function exchangeIntegral()
	{

	}

	#获取用户可使用的店铺积分
	public function useShopIntegral()
	{
		$money = Integral::getTypeBalance($this->userId,2);
		return json(['status'=>200,'msg'=>'请求成功','data'=>$money]);
	}

	#店铺积分提现
	public function  cashShopIntegral()
	{	
		$param = input('param.');
		if (null === input('param.money')) {
			return json(['status'=>-1,'msg'=>'请传参数']);
		}
		$money = Integral::getTypeBalance($this->userId,2);
		if ($money < $param['money']) {
			return json(['status'=>-1,'msg'=>'提现积分不足']);
		}
		if ($param['money'] % 100 != 0) {
			return json(['status'=>-1,'msg'=>'提现积分有误']);
		}
		$integral_rebate = UsersModel::where('id',$this->userId)->value('integral_rebate');
		if ((int)$integral_rebate <= 0 ) {
			return json(['status'=>-1,'msg'=>'请联系平台设置提现比例']);
		}

		$bank = UsersModel::where('id',$this->userId)->find();
		if (empty($bank['bank'])) {
			return json(['status'=>-1,'msg'=>'请先绑定银行卡']);
		}

		// if (md5($param['password']) != $bank['pay_password']) {
		// 	return json(['status'=>-1,'msg'=>'支付密码错误']);
		// }
		$Withdrawals = [];		//提现表数组 --Withdrawals
		$Withdrawals['order_num'] = order_sn();
		$Withdrawals['uid'] = $this->userId;
		$Withdrawals['money'] = abs($param['money']);
		$Withdrawals['real_money'] = abs($param['money']) * ($integral_rebate / 100 );
		$Withdrawals['type'] = 1;
		$Withdrawals['created_at'] = time();

		$data= [];		//用户减联盟商积分 -- integral
		$data['uid'] =  $this->userId;
		$data['value'] = abs($param['money']);
		$data['is_add'] = 2;
		$data['message'] = '积分提现';
		$data['type'] = 2;//联盟商积分
		$data['source'] = 4;
		$data['created_at'] = time();
		
		$datas= [];		//用户加积分 -- integral
		$datas['uid'] =  $this->userId;
		$datas['value'] = abs($param['money']) * ( (100 - $integral_rebate) / 100 );
		$datas['is_add'] = 1;
		$datas['message'] = '联盟商积分提现';
		$datas['type'] = 1;//消费积分
		$datas['source'] = $this->userId;
		$datas['created_at'] = time();

		Db::startTrans();
		try {
			$orderId = Db::table('withdrawals')->insertGetId($Withdrawals);
			$data['withdrawals_id'] = $orderId;
			$datas['withdrawals_id']= $orderId;
			$res  = Db::table('integral')->insert($data);
			$res1 = Db::table('integral')->insert($datas);
			if ($res && $res1) {
				Db::commit();
				return json(['status'=>200,'msg'=>'提现提交成功']);
			}
			return json(['status'=>-1,'msg'=>'提现失败']);
		} catch (Exception $e) {
			Db::rollback();
			return json(['status'=>-1,'msg'=>'提现失败']);
		}
	}
}