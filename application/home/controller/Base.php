<?php
namespace app\home\controller;

use think\Controller;
#控制层
class Base extends Controller
{	
	
	public function __construct()
	{
		parent::__construct();
		if (!isset(session('uid'))) {
			return json(['status'=>2000,'msg'=>'请登录']);
		}
	}


}