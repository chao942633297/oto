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


}