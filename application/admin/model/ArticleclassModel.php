<?php
namespace app\admin\model;

use think\Model;

#文章表
class ArticleclassModel extends Model
{   
    #文章类型
   

	protected $table = 'articleclass';
	  public function getNewsByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

	  



	   
}