<?php

namespace app\home\model;


use app\admin\model\GoodsModel;
use think\Model;

class BuycarModel extends Model{


    protected $table = 'buycar';

    public function good(){
        return $this->belongsTo(GoodsModel::class,'good_id');
    }



}
