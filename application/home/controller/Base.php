<?php
namespace app\home\controller;

use think\Controller;
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


}