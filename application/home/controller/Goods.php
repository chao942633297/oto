<?php

namespace app\home\controller;


use app\admin\model\GoodsModel;
<<<<<<< HEAD
use app\admin\model\Users;
=======
use app\admin\model\UsersModel;
>>>>>>> d6219139cdc20808b2f0254de700df924a1e09e2
use think\Controller;
use think\Db;
use think\Request;
use service\Wechat as Wechats;

class Goods extends Controller
{


    /**
     * 产品专区
     * 商品列表
     */
    public function productZone(Request $request)
    {
        $page = $request->param('page')?:1;
        $list = 10;
        $page = ($page - 1) * $list;
        /*if (empty($type) || !in_array($type, [1, 2, 3])) {
            return json(['msg' => '参数错误', 'code' => 1001]);
        }*/
        if ($request->has('type')) {                          //专区搜索
            $type = $request->param('type');
            $where['type'] = $type;
        }
<<<<<<< HEAD
        if ($request->has('class')) {                          //搜索分类
            //选择分类,则分类热度加一
            Db::table('good_class')->where('id', $request->param('class'))->setInc('hot');
            $where['class'] = $request->param('class');
        }
        if ($request->has('name')) {                          //搜索商品名称
            $where['name'] = ['like', '%' . $request->param('name') . '%'];
        }
        $where['status'] = 1;
        $goods = Db::table('good')
            ->field('id,name,img,price,rebate')
=======
        $where['type'] = $type;
        $goods = Db::table('good')
>>>>>>> d6219139cdc20808b2f0254de700df924a1e09e2
            ->where($where)
            ->limit($page, $list)
            ->order('id', 'desc')
            ->select();
<<<<<<< HEAD
        //分类
        $class = [];
        if (isset($type) && $type == 2) {
            $class = Db::table('good_class')->order('hot', 'desc')->select();
        }
        return json(['data' => $goods, 'class' => $class, 'msg' => '查询成功', 'code' => 200]);
=======
        return json(['data' => $goods, 'msg' => '查询成功', 'code' => 200]);
>>>>>>> d6219139cdc20808b2f0254de700df924a1e09e2
    }


    /*
     * 产品详情
     */
    public function goodsDetail(Request $request)
    {
        $goodId = $request->param('goodId');
        $good = GoodsModel::get($goodId);
        if (empty($good)) {
            return json(['msg' => '商品信息错误', 'code' => 200]);
        }
        $return = [];
        $return['id'] = $good['id'];
        if (isset($good['goodImg']) && !empty($good['goodImg'])) {
            foreach ($good['goodImg'] as $key => $val) {
                $return['img'][$key] = $val['imgurl'];
            }
        }
        $return['name'] = $good['name'];
        $return['price'] = $good['price'];
        $return['rebate'] = $good['rebate'];
        $return['num'] = $good['num'];
        $return['content'] = $good['content'];
        return json(['data' => $return, 'msg' => '查询成功', 'code' => 200]);
    }


    /**
     * 商品参数
     */
    public function goodsParam(Request $request)
    {
        $goodId = $request->param('goodId');
        $goodparam = Db::table('good')->where('id', $goodId)->value('parameter');
        return json(['data' => $goodparam, 'msg' => '查询成功', 'code' => 200]);
    }

<<<<<<< HEAD
=======
    #商品分享
    private function goodsShare()
    {
        #获取商品信息
        $goods = GoodsModel::where('id',input('param.id'))->select('id,thumbnail,name')->find();
        #获取jsapi 分享接口
        $jsapi_config = json_decode(Wechats::get_jsapi_config(['onMenuShareTimeline','onMenuShareAppMessage'],false,false),true);
        #获取用户唯一标识
        $unique = UsersModel::where('id',session('uid'))->value('unique');
        
        return json(['status'=>200,'msg'=>'获取jsapi','data'=>['goods'=>$goods,'jsapi_config'=>$jsapi_config,'unique'=>$unique]);       
    }

>>>>>>> d6219139cdc20808b2f0254de700df924a1e09e2

}
