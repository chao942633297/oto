<?php

namespace app\admin\controller;

use app\admin\model\GoodsModel;

class Goods extends Base
{
    //商品列表
    public function index(){
        if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = $whereb = $wherec =$brank = [];
            //商品名称查询
           /* if (isset($param['username']) && !empty($param['username'])) {
                $where['name'] = ['like', '%' . $param['username'] . '%'];
            }
            if (isset($param['status']) && $param['status'] != 0) {
                $where['is_delete'] = ['like', '%' . $param['status'] . '%'];
            }

            if($whereb){
                $brankid = db('class')->where($whereb)->column('id');
            }
            //车系查询
            if (isset($param['car']) && !empty($param['car'])) {
                $wherec['name'] = ['like', '%' . $param['car'] . '%'];
            }
            if($wherec){
                $carid = db('goods_type')->where($wherec)->column('id');
            }*/
            $good = new GoodsModel();
            $selectResult = $good->all(function($query)use($where,$offset,$limit){
                $query->order('id','desc');
                $query->where($where);
            });
            foreach($selectResult as $key=>$vo){

                $selectResult[$key]['status'] = $vo['status'] == 1 ? '上架' : '下架';
                $selectResult[$key]['type'] = db('good_class')->where(['id'=>$vo['type']])->value('name');

                $selectResult[$key]['img'] = "<img src='".$vo['img']."' width='50px' height='50px' />";
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $good->getAllGoods($where);  //总数据
            $return['rows'] = $selectResult;
            $return['data'] = $param;
            return json($return);
        }
        return $this->fetch();
    }

    //添加商品

    public function goodsAdd()
    {
        if(request()->isPost()){
            $param = input('param.');
            $param = parseParams($param['data']);
            $param['created_at'] = time();
            // var_dump($param);die;
            $good = new GoodsModel();
            $flag = $good->insertGoods($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
        $class = db('good_class')->select();
        $this->assign([
            'class'=>$class,
        ]);
        return $this->fetch();

    }

    //遍历商品的轮播图
    public function addGoodsThumbnail(){
        $param = input('param.');
        if (!empty($param['id'])) {
            $thumbnail = db('good_img')->where(['good_id'=>$param['id']])->select();
        }else{
            $thumbnail = [];
        }
        return json(['code' => 1, 'data' => $thumbnail,'id'=>$param['id'], 'msg' => 'success']);
    }

    //添加单个商品的轮播图
    public function savephoto(){
        $param = input('param.');
        $insert['good_id'] = $param['gid'];
        $insert['imgurl'] = $param['imgurl'];
        $insert['created_at'] = time();
        $result = db('good_img')->insert($insert);
        return json(['code' => $result]);
    }

    public function delThumbnail(){
        $id = input('param.id');
        // var_dump($id);die;
        $result = db('good_img')->where("id=".$id)->delete();
        return json(['code' => $result]);
    }

    //编辑商品
    public function goodsEdit(){
        $good = new GoodsModel();
        if(request()->isPost()){
            $param = input('param.');
            $param = parseParams($param['data']);
            $param['updated_at'] = time();
            $flag = $good->editGoods($param);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);

        }
        $id = input('param.id');
        $class = db('class')->select();
        $type = db('goods_type')->select();
        $goods = $good->getOneGoods($id);
        $this->assign([
            'goods' => $goods,
            'class' => $class,
            'type' => $type,
        ]);
        return $this->fetch();
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'goods/goodsedit',
                'href' => url('goods/goodsEdit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '轮播图' => [
                'auth' => 'goods/goodsthumbnail',
                'href' => "javascript:goodsthumbnail(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'glyphicon glyphicon-picture'
            ]
        ];
    }


}
