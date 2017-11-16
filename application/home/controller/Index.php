<?php

namespace app\home\controller;

use think\Controller;
use think\Db;
use think\Request;

class Index extends Controller{


    /**
     * 商城首页
     */
    public function index(){

        $carousel = Db::table('carousel')->field('pic,url')->where(['status'=>1])->select();       //轮播图
        $class = Db::table('good_class')->field('id,name,img')->where('status',1)->select();    //商品分类

    }

    /**
     * 更多分类
     */
    public function moreClass(){
        $class = Db::table('good_class')->field('id,name,img')->where('status',1)->select();

        return json(['data'=>$class,'msg'=>'查询成功','code'=>200]);
    }


    /**
     * @return \think\response\Json
     * 热门搜索
     */
    public function hotSearch(){
        $class = Db::table('good_class')
            ->field('id,name,img')
            ->where('status',1)
            ->order('hot','desc')
            ->limit(8)->select();
        return json(['data'=>$class,'msg'=>'查询成功','code'=>200]);
    }










}
