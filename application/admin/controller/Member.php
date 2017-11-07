<?php

namespace app\admin\controller;

use app\admin\model\UsersModel;
use app\admin\model\UserMoneyLog;
use app\admin\model\Apply;

use think\Db;
class Member extends Base
{
    //用户列表
    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['phone'])) {
                $where['phone'] = ['like', '%' . trim($param['phone']) . '%'];
            }
            if (!empty($param['type']) && $param['type'] != '-1') {
                $where['type'] = trim($param['type']);
            }
            if (!empty($param['nickname'])) {
                $where['nickname'] = ['like', '%' . trim($param['nickname']) . '%'];
            }

            $user = new UsersModel();
            $selectResult = $user->getListByWhere($where, $offset, $limit);


            // 拼装参数
            foreach($selectResult as $key=>$vo){
                if ($vo['pid'] >0 ) {
                    $selectResult[$key]['fathername'] = $vo->father->nickname;
                    $selectResult[$key]['fatherphone'] = $vo->father->phone;                  
                }else{
                    $selectResult[$key]['fathername'] = '';
                    $selectResult[$key]['fatherphone'] = '';
                }
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                $selectResult[$key]['type'] = UsersModel::TYPE[$vo['type']];
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }

            $return['total'] = $user->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        $this->assign('type',UsersModel::TYPE);
        return $this->fetch();
    }

    //用户列表
    public function todayindex()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $where['created_at'] = ["between",[$beginToday,$endToday]];
            if (!empty($param['phone'])) {
                $where['phone'] = ['like', '%' . trim($param['phone']) . '%'];
            }
            if (!empty($param['type']) && $param['type'] != '-1') {
                $where['type'] = trim($param['type']);
            }
            if (!empty($param['nickname'])) {
                $where['nickname'] = ['like', '%' . trim($param['nickname']) . '%'];
            }

            $user = new UsersModel();
            $selectResult = $user->getListByWhere($where, $offset, $limit);


            // 拼装参数
            foreach($selectResult as $key=>$vo){
                if ($vo['pid'] >0 ) {
                    $selectResult[$key]['fathername'] = $vo->father->nickname;
                    $selectResult[$key]['fatherphone'] = $vo->father->phone;                  
                }else{
                    $selectResult[$key]['fathername'] = '';
                    $selectResult[$key]['fatherphone'] = '';
                }
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                $selectResult[$key]['type'] = UsersModel::TYPE[$vo['type']];
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }

            $return['total'] = $user->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        $this->assign('type',UsersModel::TYPE);
        return $this->fetch();
    }

    #申请联盟商
    public function apply()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            $where['status'] = 1;
            $user = new Apply();
            $selectResult = $user->getListByWhere($where, $offset, $limit);


            // 拼装参数
            foreach($selectResult as $key=>$vo){
                if ($vo['pid'] >0 ) {
                    $selectResult[$key]['fathername'] = $vo->father->nickname;
                    $selectResult[$key]['fatherphone'] = $vo->father->phone;                  
                }else{
                    $selectResult[$key]['fathername'] = '';
                    $selectResult[$key]['fatherphone'] = '';
                }
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                $selectResult[$key]['type'] = UsersModel::TYPE[$vo['type']];
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }

            $return['total'] = $user->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        $this->assign('type',UsersModel::TYPE);
        return $this->fetch();
    }



    // 编辑用户
    public function userEdit()
    {
        $user = new UsersModel();

        if(request()->isPost()){

            $param = input('post.');

            $flag = $user->edited($param);
            $this->getSql($user);

            return json(msg($flag['code'], url('member/index'), $flag['msg']));
        }

        $id = input('param.id');

        $this->assign('user',$user->getOneInfo($id));
        return $this->fetch();
    }

    #用户金额管理
    public function user_money()
    {
        $id = input('param.id');

        $log  = new UserMoneyLog();
        $data = [];
        #获取可用会员粮票
        $data['balance'] = $log->getBalance($id,1);
        #获取冻结会员粮票
        $data['nobalance'] = $log->getBalance($id,2);
        #获取红包
        $data['red'] = $log->getTypeBalance($id,1);
        #获取分销佣金 
        $data['distribution'] = $log->getTypeBalance($id,2);
        #获取提现金额
        $data['cash'] = abs( $log->getTypeBalance($id,4) );

        #积分 -- 公用消费积分 --> 
        $all =  Db::table('integral')->where(['uid'=>$id,'type'=>1,'is_add'=>1])->sum('value');
        $all1 =  Db::table('integral')->where(['uid'=>$id,'type'=>1,'is_add'=>2])->sum('value');
        $data['score'] = sprintf("%.2f",$all-$all1);

        return json($data);          
    }

    #删除用户
    public function userDel()
    {
        $id = input('param.id');

        $role = new UsersModel();
        $flag = $role->deleted($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }


    #修改上级
    // public function edittop()
    // {
    //     $phone = input('param.phone');
    //     $id    = input('param.id');
    //     $data = UsersModel::where('phone',$phone)->find(); 
    //     if (empty($data)) {
    //         return json(['code'=>-1,'msg'=>'上级用户不存在']);
    //     }

    //     $res = UsersModel::where('id',$id)->update(['pid'=>$data->id]);

    //     if ($res) {
    //         return json(['code'=>1,'msg'=>'修改成功']);
    //     }

    //     return json(['code'=>-1,'msg'=>'修改失败']);

    // }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {   
        return [
            '编辑' => [
                'auth' => 'member/useredit',
                'href' => url('member/userEdit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '资金管理' => [
                'auth' => 'member/user_money',
                'href' => "javascript:user_money(" .$id .")",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ]              
        ];            

    }

}
