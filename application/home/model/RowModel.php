<?php

namespace app\home\model;

use app\admin\model\UsersModel;
use think\Model;

class RowModel extends Model{


    protected $table = 'row';


    public function user(){
        return $this->belongsTo(UsersModel::class,'user_id');
    }

    public function account(){
        return $this->belongsTo(AccountModel::class,'user_id','user_id');
    }



}
