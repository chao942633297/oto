<?php

namespace app\home\model;

use think\Model;

#用户收货地址模型
class Address extends Model
{
    # 确定链接表名
    protected $table = 'address';

    #获取指定用户的收货地址
    public static function getUserAddress($id)
    {
        return self::where(['uid'=>$id,'is_del'=>1])->field('id,name,phone,address,is_default,province,city,area')->select();
    }

    #添加收货地址
    public function add($param)
    {
        try{

            $result = $this->allowField(true)->save($param);
            if(false === $result){
                return false;
            }else{
                return $result;
            }
        }catch(PDOException $e){
            return false;
        }
    }

    #编辑收货地址
    public function edit($param,$userId)
    {
        try{
            
            $result = $this->allowField(true)->save($param,['id'=>$param['id'],'uid'=>$userId]);
            if(false === $result){
                return false;
            }else{
                return true;
            }
        }catch(PDOException $e){
            return false;
        }
    }
}