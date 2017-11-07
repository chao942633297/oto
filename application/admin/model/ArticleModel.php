<?php
namespace app\admin\model;

use think\Model;

#文章表
class ArticleModel extends Model
{   
    #文章类型
    const TYPE = [1=>'平台介绍',2=>'新手指南',3=>'最新公告',4=>'客服中心'];	

	protected $table = 'article';
	/**
     * 根据搜索条件获取文章列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    
    public function getNewsByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的文章数量
     * @param $where
     */
    public function getAllNews($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 插入文章
     * @param $param
     */
    public function insertNews($param)
    {
        try{
            // dump($param);die;
            // $result =  $this->validate('UserValidate')->save($param);
            $result =  $this->insert($param);
             
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '添加文章成功'];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑文章信息
     * @param $param
     */
    public function editNews($param)
    {
        try{

            $result =  $this->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '编辑文章成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据文章id获取文章信息
     * @param $id
     */
    public function getOneNews($id)
    {
        return $this->where('id', $id)->find();
    }


    /**
     * 删除文章
     * @param $id
     */
    public function delNews($id)
    {
        try{
            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除新闻成功'];

        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
   
}