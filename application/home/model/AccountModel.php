<?php

namespace app\home\model;

use think\Model;

class AccountModel extends Model{


    protected $table = 'account_log';










    public static function getAccountData($userId,$money,$remark,$from_id = ''){
        $data = [
            'user_id'=>$userId,
            'money'=>$money,
            'remark' =>$remark,
            'from_id'=>$from_id,
            'created_at'=>date('YmdHis')
        ];
        return $data;
    }




}