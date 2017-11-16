<?php

namespace app\admin\controller;

use app\admin\model\Shop as Shops;

use \think\File;
class Shop extends Base
{
    //店铺列表
    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['title'])) {
                $where['title'] = ['like', '%' . trim($param['title']) . '%'];
            }


            if (!empty($param['created_at']) ) {
                $where['created_at'] = ['>=',strtotime($param['created_at'])];
            }

            $user = new Shops();

            $selectResult = $user->getListByWhere($where, $offset, $limit);

            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['logo'] = "<img src='".$vo['logo']."' width='70px' height='50px'>";
                $imgs = unserialize($vo['lunbo']);
                $selectResult[$key]['lunbos'] = '';
                foreach ($imgs as $k => $v) {
                    $selectResult[$key]['lunbos'] .= "<img src='".$v."' width='70px' height='50px'>"."　";
                 } 
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }

            $return['total'] = $user->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }

        #获取店铺类型
        return $this->fetch();
    }

    #添加店铺
    public function add()
    {
        if(request()->isPost()){
            $data = input('param.');

            #判断手机号用户表是否已经存在此用户
            if(!preg_match("/^1[34578]\d{9}$/", $data['phone'])){
                return json(msg(1, url('shop/add'), '手机号格式错误'));  
            }
            $data['content'] = $_POST['content'];
            #封面图
            $img = request()->file('img');
            if ( isset($img) ) {
                $upload = action('File/upload');
                if ($upload['code'] == 2) {
                    return json(msg(-2,'', $upload['msg']));
                }
                $data['logo'] = $upload['data'];
            }
            #轮播图
            $imgs = request()->file('imgs');

            if ( isset($imgs) ) {
                $uploads = action('File/upload_many');
                if ($uploads['code'] == 2) {
                    return json(msg(-2,'', $uploads['msg']));
                }
                $data['lunbo'] = serialize($uploads['data']);
            }
            $data['created_at'] = time();

            $shopModel = new Shops();
            $result = $shopModel->inserted($data);

            return json(msg($result['code'], $result['data'], $result['msg']));
        }

        return $this->fetch();
    }


    #店铺信息编辑
    public function edit()
    {
        if ( request()->isPost() ) {

            $data = input('param.');
            // unset($data['id']);
            #封面图
            $img = request()->file('img');
            if ( isset($img) ) {
                $upload = action('File/upload');
                $data['logo'] = $upload['data'];
            }

            #轮播图
            $imgs = request()->file('imgs');
            if ( isset($imgs) ) {
                $uploads = action('File/upload_many');
                $data['lunbo'] = serialize($uploads['data']);
            }

            $shopModel = new Shops();
            
            $result = $shopModel->edited($data);
            return json(msg($result['code'], url('shop/index'), $result['msg']));

        }

        $data = Shops::where('id',input('param.id'))->find();
        $data->lunbo = unserialize($data->lunbo);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public static function toArray($data)
    {
        return json_decode(json_encode($data),true);
    }


    public function shopdelete()
    {
        $id  = input('param.id');
        $shopModel = new Shops();
        
        $result = $shopModel->deleted($id);
        $this->success($result['msg'],url('shop/index'));
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
                'auth' => 'shop/edit',
                'href' => url('shop/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'shop/shopdelete',
                'href' => url('shop/shopdelete', ['id' => $id]),
                'btnStyle' => 'danger',
                'icon' => 'fa fa-paste'
            ]
        ];
    }

}
