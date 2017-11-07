<?php

namespace app\admin\controller;

use think\Request;
class Carousel extends Base
{
	public function _initialize()
    {
        parent::_initialize();
        $this->db = model('Carousel');
    } 
    public function index()
    {
         if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['name'] = ['like', '%' . $param['searchText'] . '%'];
            }          
            $selectResult =  $this->db->getListByWhere($where, $offset, $limit);          
            // 拼装参数
            foreach($selectResult as $key=>$vo){ 
                $selectResult[$key]['status'] = ($vo['status']==1)?'显示':'隐藏';  
                $selectResult[$key]['pic']    = '<img src="'.$vo['pic'].'" width="150" height="50">';  
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s', $vo['created_at']);              
                $selectResult[$key]['updated_at'] = date('Y-m-d H:i:s', $vo['updated_at']);                            
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] =  $this->db->getAllCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    //添加新闻分类
    public function add()
    {
       return $this->fetch();
    }
    //修改
    public function edit(Request $request){
        $id =$request->param('id',0,'intval');  
        if(!$id){
            $this->error('非法操作');
        }
        $row = $this->db->where(['id'=>$id])->find();
        // var_dump($row);die;
        $this->assign('data',$row);
        return $this->fetch();
    }
    public function do_insert(){
       $data['name']    = $_POST['name'];
       $data['url'] = $_POST['url'];
       $id = isset($_POST['id'])?$_POST['id']:'';
       $dosubmit = $_POST['dosubmit'];
       $file = request()->file('pic');            
        if($dosubmit){         
            if(!$id){
                $this->error('非法操作');
            }
            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/news');
                if($info){                       
                    $str =  '\uploads\news\\'.$info->getSaveName();  
                    $data['pic'] = str_replace("\\","/",$str);   
                }else{
                    echo $file->getError();
                }
            }

            $data['updated_at'] = time();
            $row = $this->db->where(['id'=>$id])->find();
            if(!$row){
                $this->error('非法操作');
            }else{
                $pic = $row['pic'];
            }
            $r = $this->db->where(['id'=>$id])->data($data)->update();
            if($r){
                unlink('.'.$pic);
                $this->success('编辑成功');

            }else{
                $this->error('编辑失败');
            }
        }else{

            if($file){
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/news');
                if($info){                       
                    $str =  '\uploads\news\\'.$info->getSaveName();  
                    $data['pic'] = str_replace("\\","/",$str);   
                }else{
                    echo $file->getError();
                }
            }else{
                $this->error('请上传图片');
            }
            $data['created_at'] = time();
            $data['updated_at'] = time();
            $r = $this->db->data($data)->save();
            if($r){
                $this->success('新增成功');
            }else{
                $this->error('新增失败');
            }
        }
        
    }

    //删除
    public function del(Request $request){
        $id =$request->param('id',0,'intval'); 
        if(!$id){
            $this->error('非法操作');
        }
        $r = $this->db->where(['id'=>$id])->delete();
        return json(['code' =>1, 'msg' => '删除成功']);
    }
     private function makeButton($id)   
    {
        return [
            '编辑' => [
                'auth' => 'carousel/edit',
                'href' => url('carousel/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
           
            '删除' => [
                'auth' => 'carousel/del',
                'href' => "javascript:newsDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
            
        ];
    }
}
