<?php

namespace app\home\controller;


use app\admin\model\Users;
use app\home\model\AccountModel;
use app\home\model\RowModel;
use think\Controller;
use think\Db;
use think\Exception;
use think\Request;
use Vendor\AliPay\AlipayTradeService;
use Vendor\AliPay\Config;
use wechatH5\Notify_pub;

vendor('wechatH5.WxMainMethod');

class Notify extends Controller
{

    protected $rebate;
    protected $row_enter;
    protected $row_leave;
    protected $row_rebate;
    protected $row_leave_time;

    public function _initialize()
    {
        $this->rebate = Db::table('config')->where('name', 'rebate_num')->value('value');
        $this->row_enter = (int)Db::table('config')->where('name', 'row_enter')->value('value');
        $this->row_leave = (int)Db::table('config')->where('name', 'row_leave')->value('value');
        $this->row_rebate = Db::table('config')->where('name', 'row_rebate')->value('value');
        $this->row_leave_time = Db::table('config')->where('name', 'row_leave_time')->value('value');
    }

    /**
     * 微信回调
     */
    public function wechatNotify()
    {
        $notify = new Notify_pub();
        $xml = file_get_contents("php://input");
        //写入日志
        $notify->log_result('notify_url.log', $xml);
        $notify->saveData($xml);
        if ($notify->checkSign() == TRUE) {     //验签
            $returnData = $notify->xmlToArray($xml);
            $out_trade_no = $returnData['out_trade_no'];   //订单号
            $order = Db::table('order')->where('pay_order_num', $out_trade_no)->find();
            $total_fee = $returnData['total_fee'] / 100;    //实付金额
            /*   if($total_fee != $order['price']){
                   file_put_contents('错误信息.txt','订单id:'.$order['id'].'支付金额:'.$total_fee."\n",FILE_APPEND);
                   echo 'success';
               }*/
            if($order['is_score'] == 2){                    //充值积分
                if($order['status'] == 1){
                    //增加用户余额
                    Db::startTrans();
                    try{
                        Db::table('users')->where('id',$order['uid'])->setInc('score',$order['price']);
                        //增加余额记录
                        $score = new Score();
                        $score->insertData($order['uid'],$order['price'],'微信充值');
                        Db::commit();
                        return 'success';
                    }catch(Exception $e){
                        Db::rollback();
                        return 'fail';
                    }


                }
            }else{                                    //购买商品
                if ($order['status'] == 1) {
                    Db::startTrans();
                    try {
                        //对上级返佣
                        $this->rebate($order['uid'], $total_fee);
                        //对上级产生团队业绩
                        $this->getTeamBouns($order['uid'], $total_fee);
                        if ($order['type'] == 2) {                    //排位订单
                            //进入排位
                            $this->goQualifying($order['uid']);
                        }
                        //修改订单状态
                        Db::table('order')->where('id', $order['id'])->update(['status' => 2]);
                        Db::commit();
                        echo 'success';
                    } catch (Exception $e) {
                        Db::rollback();
                        echo 'fail';
                    }
                }

            }
        }
    }

    public function aliPayNotify(Request $request)
    {
        $arr = $request->post();
        $file = 'ali_notify.log';
        file_put_contents('123.txt','123'."\n",FILE_APPEND);
        $this->log_result($file, $arr);
        $config = Config::config();
        $alipayService = new AlipayTradeService($config);
        $result = $alipayService->check($arr);
        if ($result) {
            $orderCode = htmlspecialchars($arr['out_trade_no']);
            $order = Db::table('order')->where('pay_order_num',$orderCode)->find();
            if($arr['trade_status'] == 'TRADE_SUCCESS'){
                $total_fee = $arr['total_amount'];
                if($order['is_score'] == 2){             //充值积分
                    if($order['status'] == 1){
                        //增加用户余额
                        Db::startTrans();
                        try{
                            Db::table('users')->where('id',$order['uid'])->setInc('score',$order['price']);
                            //增加余额记录
                            $score = new Score();
                            $score->insertData($order['uid'],$order['price'],'微信充值');
                            Db::commit();
                            return 'success';
                        }catch(Exception $e){
                            Db::rollback();
                            return 'fail';
                        }
                    }
                }else{                              //购买商品
                    if($order['status'] == 1){
                        Db::startTrans();
                        try {
                            //对上级返佣
                            $this->rebate($order['uid'], $total_fee);
                            //对上级产生团队业绩
                            $this->getTeamBouns($order['uid'], $total_fee);
                            if ($order['type'] == 2) {                    //排位订单
                                //进入排位
                                $this->goQualifying($order['uid']);
                            }
                            //修改订单状态
                            Db::table('order')->where('id', $order['id'])->update(['status' => 2]);
                            Db::commit();
                            echo 'success';
                        } catch (Exception $e) {
                            Db::rollback();
                            echo 'fail';
                        }
                    }

                }
            }
        }
    }

    // 打印log
    function log_result($file, $word)
    {
        $fp = fopen($file, "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "执行日期：" . strftime("%Y-%m-%d-%H：%M：%S", time()) . "\n" . $word . "\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * @param $userId
     * @param $money
     * @param string $prentId
     * @return bool
     * 用户消费,对上级返佣
     */
    public function rebate($userId, $money, $prentId = '')
    {
        if (empty($prentId)) {
            $prentId = Db::table('users')->where('id', $userId)->value('pid');
        }
        $prent = Db::table('users')->where('id', $prentId)->find();
        $money = $money * $this->rebate * 0.01;
        Db::startTrans();
        try {
            $prent['frozen_price'] += $money;
            if ($prent['type'] == 2) {            //若用户是合伙人,则直接返回到余额里面
                $prent['balance'] += $money;
                $prent['total_price'] += $money;
            }
            Db::table('users')->update($prent);
            $list = AccountModel::getAccountData($prentId, $money, '一级分销奖', $userId);
            AccountModel::create($list);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
    }


    /**
     * @param $userId
     * @return bool
     * @throws Exception
     * 进入排位
     * 判断上面人是否出局
     * 用户级别升级为合伙人
     */
    public function goQualifying($userId)
    {
        Db::startTrans();
        try {
            $lastId = Db::table('row')->insertGetId(['user_id' => $userId, 'created_at' => date('YmdHis')]);
            //修改用户级别
            Db::table('users')->where('id', $userId)->update(['type' => 2, 'type_time' => date('YmdHis')]);
            $starId = ($lastId - $this->row_enter);
            $endId = $starId + $this->row_leave;
            $level = Db::table('row')->where(['status' => 0])->whereBetween('id', [$starId, $endId])->select();
            $acclist = [];
            foreach ($level as $key => $val) {
                Db::table('row')->where('id', $val['id'])->update(['status' => 1]);
                $userList = [
                    'id' => $val['user_id'],
                    'type' => 1,
                    'balance' => ['exp', 'balance +' . $this->row_rebate]
                ];
                Db::table('users')->where('id', $val['user_id'])->update($userList);
                $acclist[] = AccountModel::getAccountData($val['user_id'], $this->row_rebate, '分红佣金', $lastId);
            }
            Db::table('account_log')->insertAll($acclist);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage());
        }
    }


    /**
     * @return bool
     * 按照时间判断是否出局
     */
    public function judgeTime()
    {
        $leave_time = $this->row_leave_time * 86400;     //天数转换成秒
        $rowData = Db::table('row')
            ->where('created_at', '<', date('Y-m-d H:i:s', time() - $leave_time))
            ->where('status', 0)->select();
        Db::startTrans();
        try {
            $acclist = [];
            foreach ($rowData as $key => $val) {
                Db::table('row')->where('id', $val['id'])->update(['status' => 1]);
                $userList = [
                    'id' => $val['user_id'],
                    'type' => 1,
                    'balance' => ['exp', 'balance +' . $this->row_rebate]
                ];
                Db::table('users')->where('id', $val['user_id'])->update($userList);
                $acclist[] = AccountModel::getAccountData($val['user_id'], $this->row_rebate, '分红佣金', time());
            }
            Db::table('account_log')->insertAll($acclist);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }
    }

    /**
     * @param $userId
     * @param $money
     * @return bool
     * @throws \Exception
     * 用户消费
     * 产生团队业绩
     */
    public function getTeamBouns($userId, $money)
    {
        $arr = Db::table('users')->field('id,pid')->select();
        $prent = getUpUser($arr, $userId);
//        $allId = implode(',',$prent);
//        $upUsers =Db::query("select* from users WHERE id IN ($allId) ORDER BY instr('$allId',id)");
        $upUsers = Db::table('users')->whereIn('id', $prent)->select();
        $userLists = [];
        $accLists = [];
        foreach ($upUsers as $key => $val) {
            if ($val['team_switch'] == 2) {
                $userLists[] = [
                    'id' => $val['id'],
                    'balance' => ['exp', 'balance +' . $money],
                    'total_price' => ['exp', 'total_price +' . $money],
                ];
                $accLists[] = AccountModel::getAccountData($val['id'], $money, '团队业绩', $userId);
            }
        }
        Db::startTrans();
        try {
            $user = new Users();
            $user->saveAll($userLists);
            Db::table('account')->insertAll($accLists);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            return false;
        }

    }


    public function test()
    {
        $arr = [14, 9, 13];
        $allid = implode(',', $arr);
        $res = Db::query("select* from users WHERE id IN ($allid) ORDER BY instr('$allid',id)");
        dump($res);

    }


}
