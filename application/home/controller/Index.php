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
        $class = Db::table('good_class')->field('id,name,img')->where('status',1)->limit(4)->select();    //商品分类
        $lianArea = Db::table('good')
            ->where(['type'=>1,'status'=>1,'recommend'=>1])
            ->select();
        $scoreArea = Db::table('good')
            ->where(['type'=>2,'status'=>1,'recommend'=>1])
            ->select();
        $moneyArea = Db::table('good')
            ->where(['type'=>3,'status'=>1,'recommend'=>1])
            ->select();
        return json(['data'=>['carousel'=>$carousel,'class'=>$class],
            'good'=>['lianArea'=>$lianArea,'scoreArea'=>$scoreArea,'moneyArea'=>$moneyArea],
            'msg'=>'查询成功','code'=>200]);
    }

    /**
     * @return \think\response\Json
     * 首页播报,快报
     */
    public function broadcast(){
        $user = Db::table('users')->field('phone')->where('type',2)->order('updated_at','desc')->select();
        return json(['data'=>$user,'msg'=>'查询成功','code'=>200]);
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


    //平台简介
    public function brief(){
        $brief = Db::table('article')
            ->where('status',1)
            ->where('articleclass_id',1)->find();
        return json(['data'=>$brief,'msg'=>'查询成功','code'=>200]);
    }

    //新手指南
    public function newGuide(){
        $guides = Db::table('article')->field('id,title')
            ->where('status',1)
            ->where('articleclass_id',2)->select();
        return json(['data'=>$guides,'msg'=>'查询成功','code'=>200]);
    }

    //新手指南详情
    public function newGuideDetail(Request $request){
        $id = $request->param('id');
        $detail = Db::table('article')->field('title,content')
            ->where('id',$id)->find();
        return json(['data'=>$detail,'msg'=>'查询成功','code'=>200]);
    }

    //最新公告
    public function notice(){
        $guides = Db::table('article')->field('id,title')
            ->where('status',1)
            ->where('articleclass_id',3)->select();
        return json(['data'=>$guides,'msg'=>'查询成功','code'=>200]);
    }

    //最新公告详情
    public function noticeDetail(Request $request){
        $id = $request->param('id');
        $detail = Db::table('article')->field('title,content')
            ->where('id',$id)->find();
        return json(['data'=>$detail,'msg'=>'查询成功','code'=>200]);
    }

    //客服中心
    public function service(){
        $service = Db::table('article')
            ->where('status',1)
            ->where('articleclass_id',4)->find();
        return json(['data'=>$service,'msg'=>'查询成功','code'=>200]);
    }



}
