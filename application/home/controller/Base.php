<?php
namespace app\home\controller;

use think\Controller;
use app\admin\model\Config;
use app\admin\model\UserMoneyLog;


#控制层
class Base extends Controller
{	
	
	public function _initialize()
	{
		session('uid',14);
		if (null === session('uid')) {
			json(['status'=>2000,'msg'=>'请登录'])->send();
		}
	}



	/**
	*@param $uid   用户id
	*@param $type  红包类型   7:登录  8:分享  9:购物
	* 
	* self::redMoney($this->userId,$type)
	**/			
	public static function redMoney($uid,$type)
	{

		if (!in_array($type,[7,8,9])) {
			return true;
		}
		#获取配置,红包类型领取个数限制
		switch ($type) {
			case 7:	//登录
			$config = Config::getConfig('red_login');
				break;
			case 8:	//分享
			$config = Config::getConfig('red_share');

				break;
			case 9:	//购物
			$config = Config::getConfig('red_shopping');
				break;			
		}
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		$count = UserMoneyLog::where('uid',$uid)->where('type',$type)->whereBetween('created_at',[$beginToday,$endToday])->count();
		if ($count >= ceil($config) ) {
			return true;
		}
		#组装红包数据
		$data = self::redData($uid,$type);

		$model = new UserMoneyLog();
		$model->insert($data);
		return true;
	}

	#红包记录数据
	#$uid获取红包用户,$type红包类型
	protected static function redData($uid,$type)
	{
		$little = Config::getConfig('red_little');	
		$big = Config::getConfig('red_big');
		return [
			'uid'=>$uid,
			'money'=>mt_rand($little * 100,$big * 100) / 100,
			'type' =>$type,
			'is_add'=>1,
			'status'=>1,
			'source'=>0,
			'message'=>'平台红包',
			'created_at'=>time()
		];		
	} 

}