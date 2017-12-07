<?php

namespace app\home\controller;


use think\Controller;

class Test extends Controller{



    public function index(){
        if(empty($str)){
            echo 123;
        }else{
            echo 321;
        }

    }



}
