<?php

namespace app\home\controller;


use app\admin\model\GoodsModel;
use think\Controller;
use think\Db;
use think\Request;

class Goods extends Controller
{


    /**
     * 产品专区
     * 商品列表
     */
    public function productZone(Request $request)
    {
        $type = $request->param('type');
        $page = $request->param('page');
        $list = 10;
        $page = ($page - 1) * $list;
        if (empty($type) || !in_array($type, [1, 2, 3])) {
            return json(['msg' => '参数错误', 'code' => 1001]);
        }
        $where['type'] = $type;
        $goods = Db::table('good')
            ->where($where)
            ->limit($page, $list)
            ->order('id', 'desc')
            ->select();
        return json(['data' => $goods, 'msg' => '查询成功', 'code' => 200]);
    }


    /*
     * 产品详情
     */
    public function goodsDetail(Request $request)
    {
        $goodId = $request->param('goodId');
        $good = GoodsModel::get($goodId);
        if(empty($good)){
            return json(['msg'=>'商品信息错误','code'=>200]);
        }
        $return = [];
        $return['img'] = $good['goodImg'];
        $return['name'] = $good['name'];
    }


}
