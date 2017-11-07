<?php

namespace app\admin\model;

use think\Model;
#后台操作日志
class Base extends Model
{
    #物流类型
    const WL_TYPE = [1=>'顺丰快递',2=>'圆通速递',3=>'中通速递',4=>'韵达快递',5=>'申通快递']; 
    #物流编码
    const WL_CODE = [1=>'SF',2=>'YTO',3=>'ZTO',4=>'YD',5=>'STO'];

    /**
     * 根据搜索条件获取列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public static function getListByWhere($where, $offset, $limit)
    {
        return self::where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的数据数量
     * @param $where
     */
    public static function getAllCount($where)
    {
        return self::where($where)->count();
    }

    /**
     * 插入数据
     * @param $param
     */
    public function inserted($param)
    {
        try{

            $result =  $this->allowField(true)->save($param);
            if(false === $result){
                
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());

            }else{

                return msg(1, $result, '添加成功');
            }
        }catch(PDOException $e){

            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function edited($param)
    {
        try{

            $result =  $this->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url(''), '编辑成功');
            }
        }catch(PDOException $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 删除
     * @param $id
     */
    public function deleted($id)
    {
        try{

            $this->where('id', $id)->delete();
            return msg(1, '', '删除成功');

        }catch( PDOException $e){
            return msg(-1, '', $e->getMessage());
        }
    }


}