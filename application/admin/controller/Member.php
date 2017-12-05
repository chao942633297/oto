<?php

namespace app\admin\controller;

use app\admin\model\UsersModel;
use app\admin\model\UserMoneyLog;
use app\admin\model\Apply;
use app\admin\model\Message;
use app\admin\model\Withdrawals;

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
            $where['phone'] = ['<>',''];
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
                $selectResult[$key]['headimg'] = "<img src='".$vo->headimg."' width=50px; height=50px;>";
                $selectResult[$key]['wechat_qrcode'] = "<img src='".$vo->wechat_qrcode."' width=50px; height=50px;>";

                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],$vo['is_union'],$vo['team_switch']));
                $selectResult[$key]['type'] = UsersModel::TYPE[$vo['type']];
                $selectResult[$key]['is_union'] = $vo['is_union'] == 1 ? "<button class='btn btn-danger'>否</button>" : "<button class='btn btn-primary'>是</button>" ;
                $selectResult[$key]['team_switch'] = $vo['team_switch'] == 1 ? "<button class='btn btn-danger'>否</button>" : "<button class='btn btn-primary'>是</button>" ;
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
            $where['phone'] = ['<>',''];
            
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
            if (!empty($param['status']) && $param['status'] != '-1') {
                $where['status'] = trim($param['status']);
            }
            $user = new Apply();
            $selectResult = $user->getListByWhere($where, $offset, $limit);

            // 拼装参数
            foreach($selectResult as $key=>$vo){
                if ( empty($selectResult[$key]['name']) ) {
                    $selectResult[$key]['name'] = $vo->users->nickname;
                }
                if ( empty($selectResult[$key]['phone']) ) {
                    $selectResult[$key]['phone'] = $vo->users->phone;
                }
                
                $selectResult[$key]['license'] = "<img src='".$vo->license."' width=45px height=45px>";
                if ( $vo->status == 1) {
                    $selectResult[$key]['operate'] = showOperate($this->makeButtons($vo['id']));
                }else{
                    $selectResult[$key]['operate']= '';
                }
                $selectResult[$key]['status'] = Apply::STATUS[$vo['status']];
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }

            $return['total'] = $user->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        $this->assign('status',Apply::STATUS);
        return $this->fetch();
    }


    #同意拒绝 申请联盟商铺
    public function apply_yes()
    {
        $param = input('param.');
        Db::startTrans();
        try {
            $apply = new Apply();


            if ($param['status'] == 2) {
                $flag = $apply->edited($param);
                $uid = $apply->where('id',$param['id'])->value('uid');
                
                UsersModel::where('id',$uid)->update(['is_union'=>2]);
            }else{
            $param['is_del'] = 1;
            $flag = $apply->edited($param);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $flag = ['code'=>-1,'msg'=>$e->getMessage()];
        }
        return json(msg($flag['code'], url('member/apply'), $flag['msg']));
    }


    #添加联盟商提现比例
    public function integral_rebate()
    {

        if (request()->isAjax() && input('param.type') == 2) {

            $data = input('param.');
            $model = new UsersModel();
            
            $res = $model->where('id',$data['id'])->update(['integral_rebate'=>$data['value']]);            
            if ($res == 1) {
                return json(['status'=>1,'msg'=>'修改成功','data'=>url('member/index')]);
            }else{
                return json(['status'=>-1,'msg'=>'修改失败','data'=>'']);
            }
        }
        $id = input('param.id');
        $config = UsersModel::where('id',$id)->find();
        return json(['status'=>true,'msg'=>'查询成功','data'=>$config]);
    }


    // 编辑用户
    public function userEdit()
    {
        $user = new UsersModel();

        if(request()->isPost()){

            $param = input('post.');
            $data = [];
            $data['bank_name'] = $param['bank_name'];       //开户行
            $data['bank_person'] = $param['bank_person'];       //开户人
            $data['bank_id'] = $param['bank_id'];       //银行卡号
            $data['bank_zname'] = $param['bank_zname'];     //开户支行
            $param['bank'] = serialize($data);
            $flag = $user->edited($param);
            $this->getSql($user);

            return json(msg($flag['code'], url('member/index'), $flag['msg']));
        }

        $id = input('param.id');
        $data = $user->getOneInfo($id);
        $data['bank'] = unserialize($data->bank);
        $this->assign('user',$data);
        return $this->fetch();
    }

    #用户金额管理
    public function user_money()
    {
        $id = input('param.id');

        $log  = new UserMoneyLog();
        $withdrawals = new Withdrawals();
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
        $data['cash'] = $withdrawals->where(['uid'=>$id,'status'=>2])->sum('real_money');

        #消费积分 -- 公用消费积分 --> 
        $all =  Db::table('integral')->where(['uid'=>$id,'type'=>1,'is_add'=>1])->sum('value');
        $all1 =  Db::table('integral')->where(['uid'=>$id,'type'=>1,'is_add'=>2])->sum('value');

        #店铺积分
        $all2 =  Db::table('integral')->where(['uid'=>$id,'type'=>2,'is_add'=>1])->sum('value');
        $all3 =  Db::table('integral')->where(['uid'=>$id,'type'=>2,'is_add'=>2])->sum('value');

        $data['score'] = sprintf("%.2f",$all-$all1);
        $data['shop_score'] = sprintf("%.2f",$all2-$all3);


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

    #发信息
    public function user_letter()
    {
        if (request()->isPost()) {
            $data = input('param.');
            $data['content'] = $_POST['content'];
            $data['created_at'] = time();

            $msg = Db::table('user_message')->insert($data);
            if ($msg) {
                return json(['code'=>1,'msg'=>'发送成功','data'=>url('member/message')]);
            }else{
                return json(['code'=>-1,'msg'=>'发送失败']);
            }
        }

        $user = UsersModel::where('id',input('param.id'))->find();
        $this->assign('user',$user);
        return $this->fetch();

    }

    #消息列表
    public function message()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['phone'])) {
                $user = UsersModel::where('phone','like','%' . trim($param['phone']) . '%')->column('id');
                $where['uid'] = ['in',$user];
            }

            $user = new Message();
            $selectResult = $user->getListByWhere($where, $offset, $limit);


            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['username'] = $vo->user->nickname;
                $selectResult[$key]['userphone'] = $vo->user->phone;
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }

            $return['total'] = $user->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();        
    }

    #开启关闭团队奖
    public function open_team()
    {
        $open = UsersModel::where('id',input('param.id'))->value('team_switch');
        #
        if ($open == 1) {
            $res = UsersModel::where('id',input('param.id'))->update(['team_switch'=>2]);       
        }else{
            $res = UsersModel::where('id',input('param.id'))->update(['team_switch'=>1]);       
        }
        
        if ($res == 1) {
            return json(['status'=>1,'msg'=>'操作成功','data'=>url('member/index')]);
        }else{
            return json(['status'=>-1,'msg'=>'操作失败','data'=>'']);
        }        
        
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id,$type='',$status='')
    {   
        if ($type == 1 && $status==1) {
            return [
                '编辑' => [
                    'auth' => 'member/useredit',
                    'href' => url('member/userEdit', ['id' => $id]),
                    'btnStyle' => 'warning',
                    'icon' => 'fa fa-paste'
                ],
                '资金管理' => [
                    'auth' => 'member/user_money',
                    'href' => "javascript:user_money(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '发信' => [
                    'auth' => 'member/user_letter',
                    'href' => url('member/user_letter', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '开启团队奖' => [
                    'auth' => 'member/open_team',
                    'href' => "javascript:open_team(" .$id .")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-paste'
                ]                                   
            ];            
        }elseif ($type == 1 && $status==2) {
            return [
                '编辑' => [
                    'auth' => 'member/useredit',
                    'href' => url('member/userEdit', ['id' => $id]),
                    'btnStyle' => 'warning',
                    'icon' => 'fa fa-paste'
                ],
                '资金管理' => [
                    'auth' => 'member/user_money',
                    'href' => "javascript:user_money(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '发信' => [
                    'auth' => 'member/user_letter',
                    'href' => url('member/user_letter', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '关闭团队奖' => [
                    'auth' => 'member/open_team',
                    'href' => "javascript:open_team(" .$id .")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-paste'
                ]                                   
            ];     
        }else if ($type == 2 && $status == 1) {
            return [
                '编辑' => [
                    'auth' => 'member/useredit',
                    'href' => url('member/userEdit', ['id' => $id]),
                    'btnStyle' => 'warning',
                    'icon' => 'fa fa-paste'
                ],
                '资金管理' => [
                    'auth' => 'member/user_money',
                    'href' => "javascript:user_money(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '发信' => [
                    'auth' => 'member/user_letter',
                    'href' => url('member/user_letter', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '积分提现比例' => [
                    'auth' => 'member/integral_rebate',
                    'href' => "javascript:integral_rebate(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '开启团队奖' => [
                    'auth' => 'member/open_team',
                    'href' => "javascript:open_team(" .$id .")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-paste'
                ]                                                      
            ];  
        }else{
            return [
                '编辑' => [
                    'auth' => 'member/useredit',
                    'href' => url('member/userEdit', ['id' => $id]),
                    'btnStyle' => 'warning',
                    'icon' => 'fa fa-paste'
                ],
                '资金管理' => [
                    'auth' => 'member/user_money',
                    'href' => "javascript:user_money(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '发信' => [
                    'auth' => 'member/user_letter',
                    'href' => url('member/user_letter', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                // '积分提现比例' => [
                //     'auth' => 'member/integral_rebate',
                //     'href' => "javascript:integral_rebate(" .$id .")",
                //     'btnStyle' => 'primary',
                //     'icon' => 'fa fa-paste'
                // ],
                // '关闭团队奖' => [
                //     'auth' => 'member/open_team',
                //     'href' => "javascript:open_team(" .$id .")",
                //     'btnStyle' => 'danger',
                //     'icon' => 'fa fa-paste'
                // ]                                                      
            ];            
        }
          

    }


    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButtons($id)
    {   
        return [
            '同意' => [
                'auth' => 'member/apply_yes',
                'href' => "javascript:apply_yes(" .$id . ",". 2 .")",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '驳回' => [
                'auth' => 'member/apply_yes',
                'href' => "javascript:apply_yes(" .$id . ",". 3 .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-paste'
            ]              
        ];

    }
}
