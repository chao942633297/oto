<?php
namespace app\admin\controller;

use app\admin\model\UserMoneyLog;
use app\admin\model\Integral;
use app\admin\model\UsersModel;
use app\admin\model\Withdrawals;
use think\Db;
#财务对账控制器
class Finance extends Base
{
    #粮票
	public function index()
	{
		
		if ( request()->isAjax() ) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            #条件筛选
            if (!empty($param['phone'])) {
                $user = UsersModel::where('phone','like','%' . trim($param['phone']) . '%')->column('id');
            	$where['uid'] = ['in',$user];	
            }
            if (!empty($param['type']) && $param['type'] != '-1') {
                $where['type'] = trim($param['type']);
            }
            if (!empty($param['status']) && $param['status'] != '-1') {
                $where['status'] = trim($param['status']);
            }
            if (!empty($param['created_at']) ) {
                $where['created_at'] = ['>=',strtotime($param['created_at'])];
            }

            $order = new UserMoneyLog();
            $selectResult = $order->getListByWhere($where, $offset, $limit);


            // 拼装参数
            foreach($selectResult as $key=>$vo){

            	$selectResult[$key]['username'] = $vo->user['nickname'];
            	$selectResult[$key]['userphone'] = $vo->user['phone'];
                $selectResult[$key]['money'] = $vo['is_add'] == 1 ? $vo['money']:-$vo['money'];
            	$selectResult[$key]['is_add'] = $vo['is_add'] == 1 ? '增加' :'减少';
                $selectResult[$key]['status'] = $vo['status'] ? UserMoneyLog::STATUS[$vo['status']] :'';
                $selectResult[$key]['type'] = $vo['type'] ? UserMoneyLog::TYPE[$vo['type']] :'';
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }
            $return['total'] = $order->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);			 
		}

		$this->assign('status',UserMoneyLog::STATUS);   //粮票状态
		$this->assign('type',UserMoneyLog::TYPE); //粮票来源
		return $this->fetch();
	}

    #积分
	public function score()
	{
		
		if ( request()->isAjax() ) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            #条件筛选
            if (!empty($param['phone'])) {
                $user = UsersModel::where('phone','like','%' . trim($param['phone']) . '%')->column('id');
                $where['uid'] = ['in',$user];
            }

            if (!empty($param['type']) && $param['type'] != '-1') {
                $where['type'] = trim($param['type']);
            }

            if (!empty($param['source']) && $param['source'] != '-1') {
                $where['source'] = trim($param['source']);
            }
            if (!empty($param['created_at']) ) {
                $where['created_at'] = ['>=',strtotime($param['created_at'])];
            }

            $order = new Integral();
            $selectResult = $order->getListByWhere($where, $offset, $limit);


            // 拼装参数
            foreach($selectResult as $key=>$vo){

            	$selectResult[$key]['username'] = $vo->user->nickname;
            	$selectResult[$key]['userphone'] = $vo->user->phone;
                $selectResult[$key]['type'] = $vo['type'] ? Integral::TYPE[$vo['type']] :'';
                $selectResult[$key]['value'] = $vo['is_add'] == 1 ? $vo['value']  : - $vo['value'];
                $selectResult[$key]['source'] = $vo['source'] ? Integral::SOURCE[$vo['source']] :'';
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
            }

            $return['total'] = $order->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);			 
		}

		$this->assign('type',Integral::TYPE);   //积分类型
        $this->assign('source',Integral::SOURCE);   //积分类型
		return $this->fetch();
	}

    #提现
    public function cash()
    {
        
        if ( request()->isAjax() ) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            #条件筛选
            if (!empty($param['phone'])) {
                $user = UsersModel::where('phone','like','%' . trim($param['phone']) . '%')->column('id');
                $where['uid'] = ['in',$user];
            }

            if (!empty($param['type']) && $param['type'] != '-1') {
                $where['type'] = trim($param['type']);
            }

            if (!empty($param['status']) && $param['status'] != '-1') {
                $where['status'] = trim($param['status']);
            }
            if (!empty($param['created_at']) ) {
                $where['created_at'] = ['>=',strtotime($param['created_at'])];
            }

            $order = new Withdrawals();
            $selectResult = $order->getListByWhere($where, $offset, $limit);


            // 拼装参数
            foreach($selectResult as $key=>$vo){

                $selectResult[$key]['username'] = $vo->user['nickname'];
                $selectResult[$key]['userphone'] = $vo->user['phone'];
                $selectResult[$key]['type'] = $vo['type'] ? Withdrawals::TYPE[$vo['type']] :'';
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s',$vo['created_at']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],$vo['status']));
                $selectResult[$key]['status'] = $vo['status'] ? Withdrawals::STATUS[$vo['status']] :'';
            }

            $return['total'] = $order->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);            
        }

        $this->assign('type',Withdrawals::TYPE);   //积分类型
        $this->assign('status',Withdrawals::STATUS);   //积分类型
        return $this->fetch();
    }


    #提现改状态
    public function user_status()
    {
        $post = input('param.');
        Db::startTrans();
        $cash = new Withdrawals();
        // $flag = [];
        try {
            $res = $cash->where('id',$post['id'])->update(['status'=>$post['status']]);
            #修改订单状态  2:同意  3:驳回
            if ($post['status'] == 3) {
                $one = $cash->getOneInfo($post['id']); 
                if ($one['type'] == 1) {
                    #驳回,积分返还
                    Integral::where('withdrawals_id',$one['id'])->update(['is_add'=>1]);
                }elseif($one['type']==2){
                    #驳回,粮票返还
                    UserMoneyLog::where('withdrawals_id',$one['id'])->update(['is_add'=>1,'message'=>'提现驳回']);
                }
            }
            Db::commit();
            $flag = ['code'=>$res,'msg'=>'操作成功','data'=>url('finance/cash')];
        } catch ( Expection $e) {
            Db::rollback();
            $flag  = ['code'=>-1,'msg'=>$e->getMessage(),'data'=>url('finance/cash')];
        
        }
        return json($flag);
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id,$type)
    {   
        if ($type == 1) {
            return [
                '同意' => [
                    'auth' => 'finance/user_status',
                    'href' => "javascript:user_status(" .$id. ",". 2 .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '驳回' => [
                    'auth' => 'finance/user_status',
                    'href' => "javascript:user_status(" .$id. ",". 3 .")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-paste'
                ]                            
            ];             
        }
        $name = Withdrawals::STATUS[$type];
        return [
            "$name" => [
            'auth' => 'finance/user_status',
            'href' => "javascript:;",
            'btnStyle' => 'primary',
            'icon' => 'fa fa-paste'
            ]              
        ]; 
           

    }
}