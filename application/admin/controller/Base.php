<?php

namespace app\admin\controller;

use think\Controller;
use app\admin\model\OperateLog;

class Base extends Controller
{
    public function _initialize()
    {
        if(empty(session('username'))){

            $loginUrl = url('login/index');
            if(request()->isAjax()){
                return msg(111, $loginUrl, '登录超时');
            }

            $this->redirect($loginUrl);
        }

        // 检测权限
        $control = lcfirst(request()->controller());
        $action = lcfirst(request()->action());

        if(empty(authCheck($control . '/' . $action))){
            if(request()->isAjax()){
                return msg(403, '', '您没有权限');
            }

            $this->error('403 您没有权限');
        }

        $this->assign([
            'username' => session('username'),
            'rolename' => session('role')
        ]);

    }

    #获取执行的sql语句,写入Operatelog表
    public static function getSql($model)
    {   

        $sql = $model->getLastsql();
        
        OperateLog::writeSqlLog($sql);
    }
}