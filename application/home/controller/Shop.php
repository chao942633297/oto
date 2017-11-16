<?php
namespace app\home\controller;

use think\Controller;
use think\Db;
use app\admin\model\Shop as Shops;
#店铺展示
class Shop extends Controller
{	

	#店铺列表
	public function shopList()
	{
        null != input('param.page') ?$page = input('param.page') :$page = 1 ; //页数
        $list = 10;	//每页条数
        $page = ($page - 1) * $list;

        $list = Db::table('shop')
            ->limit($page, $list)
            ->order('id', 'desc')
            ->select();
        if (!empty($list)) {
	        foreach ($list as $k => $v) {
	        	$list[$k]['lunbo'] = unserialize($v['lunbo']);
	        }        	
        }

        return json(['data' => $list ? $list :[], 'msg' => '查询成功', 'status' => 200]);
	}


	#店铺详情
	public function shopDetail()
	{
		$shop = Shops::where('id',input('param.id'))->find();
		return json(['data' => $shop ? $shop :[], 'msg' => '查询成功', 'status' => 200]);
	}
}