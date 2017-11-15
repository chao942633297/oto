<?php

namespace app\home\controller;

use think\Controller;
use think\Db;

class Index extends Controller{

    public function index(){

        $carousel = Db::table('carousel')->field('pic,url')->where(['status'=>1])->select();       //轮播图


    }





}
