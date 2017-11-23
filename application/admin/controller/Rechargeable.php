<?php

namespace app\admin\controller;
use app\admin\model\UsersModel;
use app\admin\model\Rechargeables;

/**
* 充值卡 
*/
class Rechargeable extends Base
{

    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['order_num'])) {
                $where['card_number'] = trim($param['order_num']);
            }

            $user = new Rechargeables();

            $selectResult = $user->getListByWhere($where, $offset, $limit);
            foreach ($selectResult as $k => $v) {
            	$selectResult[$k]['card_password'] = '<input type="password" value="'.$v->card_password.'" style="background:none;outline:none;border:0px;width:50%" id="card_password'.$v->id.'" readonly></input><img src="/static/admin/images/glass.png" onclick="switchs('.$v->id.')">';
            	if ($v['status'] == 1) {
                	$selectResult[$k]['operate'] = showOperate($this->makeButton($v['id']));
            		$selectResult[$k]['status'] = "<span class='btn btn-danger'>".Rechargeables::STATUS[$v->status]."</span>";
            	}elseif ($v['status'] == 2) {
            		$selectResult[$k]['operate'] = '';
            		$selectResult[$k]['status'] = "<span class='btn btn-primary'>".Rechargeables::STATUS[$v->status]."</span>";
            	}else{
            		$selectResult[$k]['operate'] = '';
            		$selectResult[$k]['status'] = "<span class='btn btn-warning'>".Rechargeables::STATUS[$v->status]."</span>";
            		
            	}
            }
            $return['total'] = $user->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }

        return $this->fetch();
    }

    #生成充值卡
    public function add()
    {
    	$data = [];
    	$data['card_number'] = 'OTO'.(intval(date('Y')) - 2011) . strtoupper(dechex(date('m'))) . date('d') .time(). substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    	$data['card_password'] = getRandomString(6);
    	$data['created_at'] = date('Y-m-d H:i:s');
    	$res = Rechargeables::insert($data);
    	if ($res == 1) {
            return json(['code'=>1,'msg'=>'生成成功','data'=>url('rechargeables/index')]);
        }else{
            return json(['status'=>-1,'msg'=>'生成失败','data'=>'']);
        }

    }

    #出售
    public function sell()
    {
    	$id = input('param.id');
    	$value = Rechargeables::where('id',$id)->value('value');
    	if ((int)$value <=0) {
            return json(['code'=>-1,'msg'=>'请先设置此卡的金额','data'=>'']);
    	}
    	$res = Rechargeables::where('id',$id)->update(['status'=>2]);
    	if ($res == 1) {
            return json(['code'=>1,'msg'=>'出售成功','data'=>url('rechargeables/index')]);
        }else{
            return json(['code'=>-1,'msg'=>'出售失败','data'=>'']);
        }    	
    }

    #设置金额
    public function set_value()
    {
    	if (request()->isAjax() && input('param.type') == 2) {
    		$param = input('param.');
	    	$res = Rechargeables::where('card_number',$param['card_number'])->update(['value'=>abs((int)$param['value'])]);
	    	if ($res == 1) {
	            return json(['status'=>1,'msg'=>'设置成功','data'=>url('rechargeable/index')]);
	        }else{
	            return json(['status'=>-1,'msg'=>'设置失败','data'=>'']);
	        }      		
    	}
    	$card_number = Rechargeables::where('id',input('param.id'))->value('card_number');
    	return json($card_number);
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {   
        return [
            '出售' => [
                'auth' => 'rechargeable/sell',
                'href' => "javascript:sell(" .$id .")",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '设置金额' => [
                'auth' => 'rechargeable/set_value',
                'href' => "javascript:set_value(" .$id .")",
                'btnStyle' => 'warning',
                'icon' => 'fa fa-paste'
            ],
        ];             
    }


}

function getRandomString($len, $chars=null)
{
    if (is_null($chars)){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    }  
    mt_srand(10000000*(double)microtime());
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
        $str .= $chars[mt_rand(0, $lc)];  
    }
    return $str;
}