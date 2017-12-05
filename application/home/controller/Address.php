<?php
namespace app\home\controller;

use app\admin\model\UsersModel;
use app\home\model\Address as Addresss;

use think\Controller;
use think\Db;
#用户地址管理
class Address extends Base
{	
	#用户ID
	protected $userId;
	
	public function _initialize()
	{
		parent::_initialize();
		$this->userId = session('uid');
	}

	#我的收货地址
	public function myAddress()
	{
		$data = Addresss::getUserAddress($this->userId);
		return json(['status'=>200,'message'=>'请求成功','data'=>$data?:'']);
	}


	#添加收货地址
	public function addAddress()
	{	
		$param = input('param.');
		if (empty($param)) {
	    	return json(['status'=>401,'message'=>'请传参数']);
		}
    	if ( $param['is_default'] == 2 ) {

    		if( Addresss::where(['uid'=>$this->userId,'is_default'=>2])->count() ){
	    		$res = Addresss::where('uid',$this->userId)->update(['is_default'=>1]);	
    		}else{
    			$res = 1;
    		}

	    	if (!$res) {
	    		return json(['status'=>401,'message'=>'系统内部错误','data'=>'']);
	    	}
    	}


    	$data = [];
    	$data['uid'] = $this->userId;
    	$data['name'] = $param['name'];
    	$data['phone'] = $param['phone'];
    	$data['province'] = $param['province'];
    	$data['city'] = $param['city'];
    	$data['area'] = $param['area'];
    	$data['address'] = $param['address'];
    	$data['is_default'] = $param['is_default'];
    	$data['created_at'] = time();
    	$data['updated_at'] = time();
    	$add = new Addresss();
		$res1 = $add->insert($data);
		
		if ($res1) {
    		return json(['status'=>200,'message'=>'添加成功','data'=>'']);
		}
    	
    	return json(['status'=>401,'message'=>'添加失败','data'=>'']);

	}

	#获取单条地址
	public function getOneAddress()
	{
		$data = Addresss::where('id',input('param.id'))->find();
		return json(['status'=>200,'message'=>'查询成功','data'=>$data]);
	}

	#确认编辑收货地址
	public function editAddress()
	{
		$param = input('param.');
		$data = [];

		$data['name'] = $param['name'];
		$data['phone'] = $param['phone'];
    	// $data['guhua'] = $param['guhua'];
		$data['province'] = $param['province'];
		$data['city'] = $param['city'];
		$data['area'] = $param['area'];
		$data['address'] = $param['address'];
		$data['updated_at'] = time();
		
		if (!input('param.id') || input('param.id') == '') {
			return json(['status'=>300,'message'=>'缺少参数','data'=>'']);
		}
		#开启事物
		Db::startTrans();
		$res = Addresss::where('id',input('param.id'))->update($data);
		if ($res) {
			#提交事物
			Db::commit();
			return json(['status'=>200,'message'=>'编辑成功','data'=>'']);
		}else{
			#回滚事物
			Db::rollback();
			return json(['status'=>401,'message'=>'编辑失败','data'=>'']);
		}

		
	}

	#删除收货地址
	public function deleteAddress()
	{
		$id = input('param.id');
		
		if (Addresss::where('id',$id)->value('is_default') == 2) {
			return json(['status'=>401,'message'=>'默认地址不可删除','data'=>'']);
		}

		$res = Addresss::where(['id'=>$id,'uid'=>$this->userId])->update(['is_del'=>2,'updated_at'=>time()]);

		if ($res) {
			return json(['status'=>200,'message'=>'删除成功','data'=>'']);			
		}
		return json(['status'=>401,'message'=>'删除失败','data'=>'']);
	}

	#设置默认收货地址
	public function setDefault()
	{
		$id = input('param.id');

		#设置用户的收货地址全部
		$res = Addresss::where('uid',$this->userId)->update(['is_default'=>1,'updated_at'=>time()]);

		if (!$res) {
			return json(['status'=>401,'message'=>'解除默认地址失败','data'=>'']);
		}

		$res1 = Addresss::where(['id'=>$id,'uid'=>$this->userId])->update(['is_default'=>2,'updated_at'=>time()]);

		if ($res1) {
			return json(['status'=>200,'message'=>'设置成功','data'=>'']);
		}
		
		return json(['status'=>401,'message'=>'设置默认地址失败','data'=>'']);
	}

}