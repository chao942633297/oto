<?php

namespace app\admin\controller;

use app\admin\model\UsersModel;
use app\admin\model\UsersMoneyLog;
use app\admin\model\Coupon;
class Users extends Base
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
            $selectResult = $user->getUsersByWhere($where, $offset, $limit);


            // 拼装参数
            foreach($selectResult as $key=>$vo){
                    $selectResult[$key]['check'] = "<input type='checkbox' data-id='".$vo->id."' class='check'>";
                if ($vo['pid'] >0 ) {
                    $selectResult[$key]['fathername'] = $vo->father->nickname;
                    $selectResult[$key]['fatherphone'] = $vo->father->phone;                  
                }else{
                    $selectResult[$key]['fathername'] = '';
                    $selectResult[$key]['fatherphone'] = '';
                }
                #根据身份匹配按钮
                if ( $vo['type'] == UsersModel::SHOP ) {
                    $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],$vo['shop_id'],1));
                }else{
                    $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                }
                $selectResult[$key]['type'] = UsersModel::TYPE[$vo['type']];
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }

            $return['total'] = $user->getAllUsers($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        $this->assign('type',UsersModel::TYPE);
        return $this->fetch();
    }


    // 添加用户
    public function userAdd()
    {
        if(request()->isPost()){

            $param = input('param.');
            $param['password'] = md5($param['password']);
            $user = new UsersModel();
            $flag = $user->insertUser($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $role = new RoleModel();
        $this->assign([
            'role' => $role->getRole(),
            'status' => config('user_status')
        ]);

        return $this->fetch();
    }

    // 编辑用户
    public function userEdit()
    {
        $user = new UsersModel();

        if(request()->isPost()){

            $param = input('post.');
            #判断修改的用户生日是否已填写
            if ( $user->where('id',$param['id'])->value('birthday') != '0000-00-00' ) {
                unset($param['birthday']);
            }

            $flag = $user->editUser($param);
            $this->getSql($user);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');

        $this->assign('user',$user->getOneUser($id));
        return $this->fetch();
    }

    #用户金额管理
    public function user_money()
    {
        $id = input('param.id');

        $log  = new UsersMoneyLog();
        $data = [];
        #获取余额
        $data['balance'] = $log->getBalance($id);
        #获取生日红包
        $data['birthday'] = $log->getTypeBalance($id,UsersMoneyLog::BIRTHDAY);
        #获取分销奖金
        $data['distribution'] = $log->getTypeBalance($id,UsersMoneyLog::DISTRIBUTION);
        #获取开拓奖金
        $data['open'] = $log->getTypeBalance($id,UsersMoneyLog::OPEN);
        #获取点赞奖金
        $data['likes'] = $log->getTypeBalance($id,UsersMoneyLog::LIKES);
        #获取提现金额
        $data['cash'] = abs( $log->getTypeBalance($id,UsersMoneyLog::CASH) );

        return json($data);          
    }

    #用户详情
    public function user_detail()
    {
        $id = input('param.id');

        $user = new UsersModel();
        $data = $user->getOneUser($id);

        if ( $data->shop_id ) {
            $data['shopname'] = $data->shop->title;
        }else{
            $data['shopname'] = '';
        }

        return json($data);
    }

    #查看店铺小哥
    public function look_brother()
    {
        $shop_id = input('param.id');
        $data = UsersModel::where('shop_id',$shop_id)->select();
        return json($data);
    }

    #删除用户
    public function userDel()
    {
        $id = input('param.id');

        $role = new UsersModel();
        $flag = $role->delUser($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }


    public function sendCouponList()
    {
        $data = Coupon::where(['uid'=>0,'is_use'=>1])->select();
        foreach ($data as $k => $v) {
            $data[$k]['type'] = Coupon::TYPE[$v->type];
        }
        return json($data);
    }

    #赠送优惠券
    public function sendCoupon()
    {
        $param = input('param.');
        $uid = explode(",",ltrim($param['id'],","));

        $cop = Coupon::where('id',$param['coupon_id'])->find();
        $data = [];
        foreach ($uid as $k => $v) {
            $data[$k]['uid'] = $v; 
            $data[$k]['type'] = $cop->type; 
            $data[$k]['goods_type'] = $cop->goods_type; 
            $data[$k]['full_money'] = $cop->full_money; 
            $data[$k]['reduce_money'] = $cop->reduce_money;
            $data[$k]['reduce_money'] = $cop->reduce_money; 
            $data[$k]['minus'] = $cop->minus;
            $data[$k]['is_use'] = 1;
            $data[$k]['created_at'] = time();        
        }

        if(Coupon::insertAll($data)){
            return json(['code'=>1,'msg'=>'赠送成功','data'=>url('users/index')]);
        }
        return json(['code'=>-1,'msg'=>'赠送失败','data'=>'']);

    }

    #修改上级
    public function edittop()
    {
        $phone = input('param.phone');
        $id    = input('param.id');
        $data = UsersModel::where('phone',$phone)->find(); 
        if (empty($data)) {
            return json(['code'=>-1,'msg'=>'上级用户不存在']);
        }

        $res = UsersModel::where('id',$id)->update(['pid'=>$data->id]);

        if ($res) {
            return json(['code'=>1,'msg'=>'修改成功']);
        }

        return json(['code'=>-1,'msg'=>'修改失败']);

    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id,$shop_id='',$type='')
    {   
        if ( $type == 1 ) {
            return [
                '修改上级' => [
                    'auth' => 'users/edittop',
                    'href' => "javascript:edittop(" .$id .")",
                    'btnStyle' => 'warning',
                    'icon' => 'fa fa-paste'
                ],  
                '编辑' => [
                    'auth' => 'users/useredit',
                    'href' => url('users/userEdit', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '详情' => [
                    'auth' => 'users/user_detail',
                    'href' => "javascript:user_detail(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '资金管理' => [
                    'auth' => 'users/user_money',
                    'href' => "javascript:user_money(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '查看小哥' => [
                    'auth' => 'users/look_brother',
                    'href' => "javascript:lookBrother(" .$shop_id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ]                
            ];            
        }else{
            return [
                '修改上级' => [
                    'auth' => 'users/edittop',
                    'href' => "javascript:edittop(" .$id .")",
                    'btnStyle' => 'warning',
                    'icon' => 'fa fa-paste'
                ],  
                '编辑' => [
                    'auth' => 'users/useredit',
                    'href' => url('users/userEdit', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '详情' => [
                    'auth' => 'users/user_detail',
                    'href' => "javascript:user_detail(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '资金管理' => [
                    'auth' => 'users/user_money',
                    'href' => "javascript:user_money(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ]
            ];            
        }

    }

    public static function test()
    {
        echo 1;
    }

}
