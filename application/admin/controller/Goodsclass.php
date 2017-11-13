<?php

namespace app\admin\controller;

use think\Controller;

class Goodsclass extends Controller{

    //分类列表
    public function index(){
        if(request()->isAjax()) {
            $selectResult = db('good_class')->select();
            foreach ($selectResult as $key => $val) {
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s', $val['created_at']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($val['id']));
            }
            $return['total'] = count($selectResult);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }


    public function editclass(){
        if(request()->isPost()){
            $param['name'] = input('name');
            $param['id'] = input('id');
            $param['updated_at'] = time();
            $flag = db('good_class')->update($param);
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

