<?php

namespace app\admin\controller;

use think\Controller;

class Goodsclass extends Controller{

    /**
     * @return mixed|\think\response\Json
     * 分类列表
     */
    public function index(){
        if(request()->isAjax()) {
            $selectResult = db('good_class')->select();
            $status = [
                '1'=>'推荐',
                '2'=>'未推荐'
            ];
            foreach ($selectResult as $key => $val) {
                $selectResult[$key]['img'] = "<img src='".$val['img']."' with='100px' height='100px' />";
                $selectResult[$key]['status'] = $status[$val['status']];
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s', $val['created_at']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($val['id']));
            }
            $return['total'] = count($selectResult);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }

    /**
     * @return mixed|\think\response\Json
     * 增加分类
     */
    public function addclass(){
         if(request()->isAjax()){
             $param = input('param.');
             $param = parseParams($param['data']);
             $param['created_at'] = time();
             $param['updated_at'] = time();
             db('good_class')->insert($param);
             return json(['code' => 1, 'data' => '', 'msg' => '添加分类成功']);
         }
        return $this->fetch();
    }


    /**
     * @return mixed|\think\response\Json
     * @throws \think\Exception
     * 编辑分类
     */
    public function editclass(){
        if(request()->isPost()){
            $param = input('param.');
            $param = parseParams($param['data']);
            $param['updated_at'] = time();
            db('good_class')->update($param);
            return json(['code' => 1, 'data' => '', 'msg' => '编辑分类成功']);
        }
        $id = input('param.id');
        $class = db('good_class')->where('id',$id)->find();
        $this->assign([
            'class' => $class,
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
                'auth' => 'goodsclass/editclass',
                'href' => url('goodsclass/editclass', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
        ];
    }



}

