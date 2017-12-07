<?php

namespace app\admin\controller;

use think\Request;
use app\admin\model\ArticleModel;
use app\admin\model\ArticleclassModel;
class Article extends Base
{
    protected $db;

	public function _initialize(){
        parent::_initialize();
        $this->db = model('ArticleModel');
        $this->dbclass = model('ArticleclassModel');
    } 
    public function index()
    {
         if(request()->isAjax()){
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['searchText'])) {
                $where['title'] = ['like', '%' . $param['searchText'] . '%'];
            }          
            $selectResult =  $this->db->getNewsByWhere($where, $offset, $limit); 
            $articleclass=$this->dbclass->select();         
            // 拼装参数
            foreach($selectResult as $key=>$vo){ 
                $selectResult[$key]['status'] = ($vo['status']==1)?'显示':'隐藏';  
                $selectResult[$key]['pic']    = '<img src="'.$vo['pic'].'" width="50" height="50">';  
                $selectResult[$key]['created_at'] = date('Y-m-d H:i:s', $vo['created_at']);              
                $selectResult[$key]['updated_at'] = date('Y-m-d H:i:s', $vo['updated_at']);                            
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
                foreach ($articleclass as $k => $v) {
                    if($vo['articleclass_id'] ==$v['id']){
                        $selectResult[$key]['classname']=$v['name'];
                    }
                }
            }

            $return['total'] =  $this->db->getAllNews($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    //添加新闻分类
    public function add_class()
    {
        
        $list=$this->common();
        $this->assign('list',$list);
        return $this->fetch();
    }
    //修改
    public function edit(Request $request){
        $id =$request->param('id',0,'intval');  
        if(!$id){
            $this->error('非法操作');
        }
        $row = $this->db->where(['id'=>$id])->find();

        $list=$this->common();             
        $this->assign('list',$list);    
        $this->assign('data',$row);     
        return $this->fetch();
    }
    public function do_insert(){
     
        $data['articleclass_id']    = $_POST['pid'];
        $data['title']    = $_POST['title'];
        
        $data['status']=isset($_POST['status'])?$_POST['status']: 0;
        $data['quantity'] = $_POST['quantity'];
        $data['content']  = $_POST['content'];
        $id = isset($_POST['id'])?$_POST['id']:'';
        $dosubmit = $_POST['dosubmit'];
        $file = request()->file('pic');            
        if($dosubmit){
            // var_dump($id);die;
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
                if($pic){
                    unlink('.'.$pic);
                }

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
                $this->error('请上传封面');
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
    public function newsdel(Request $request){
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
                'auth' => 'article/edit',
                'href' => url('article/edit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'article/newsdel',
                'href' => "javascript:newsDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
            
        ];
    }
/**************************************************************************************************************************************************************************************************************************************************************
*文章分类
*
*/
        #分类列表
        public function articleindex(){

            if(request()->isAjax()){
                $param = input('param.');
                $limit = $param['pageSize'];
                $offset = ($param['pageNumber'] - 1) * $limit;
                $where = [];
                if (!empty($param['searchText'])) {
                    $where['title'] = ['like', '%' . $param['searchText'] . '%'];
                }          
                $selectResult =  $this->dbclass->getNewsByWhere($where, $offset, $limit);  

                // 拼装参数
                foreach($selectResult as $key=>$vo){ 

                    $selectResult[$key]['pname'] = $this->dbclass->where('id',$vo['pid'])->value('name');               
                    $selectResult[$key]['created_at'] = date('Y-m-d H:i:s', $vo['created_at']);              
                    $selectResult[$key]['updated_at'] = date('Y-m-d H:i:s', $vo['updated_at']);                            
                    $selectResult[$key]['operate'] = showOperate($this->makeclassButton($vo['id']));
                }

                $return['total'] =  $this->db->getAllNews($where);  //总数据
                $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
        }
        #添加分类
        public function articleadd(Request $request){

            $list=$this->common();
            $id =$request->param('pid');
            $name=$request->param('name');
             if($_POST){

                $class=$this->dbclass->where('id',$id)->find();

                $data=[];
                $data['pid']=$id;
                $data['name']=$name;
                $data['path']=$class['path'].','.$id;
                $res=$this->dbclass->insert($data);
                if($res){
                    $this->success('新增成功');
                }else{
                    $this->success('新增失败');
                }
             }
             
            $this->assign('list',$list);
            return $this->fetch();
        }

        public function articleedit(Request $request){
            $id =$request->param('id',0,'intval');
            $list=$this->common(); 
            $data=$this->db->where('id',$id)->find();
            $this->assign('data',$data);            
            $this->assign('list',$list);

            return $this->fetch();
        }

        public function articledel(Request $request){
            $id =$request->param('id',0,'intval');
            $ress=$this->dbclass->where('pid',$id)->find();
            if($ress){
                return json(['code' =>3, 'msg' => '存在下级，删除失败']);
            }


            $res=$this->dbclass->where('id',$id)->delete();
            if($res){
                return json(['code' =>1, 'msg' => '删除成功']);
            }
        }

        
        #查询分类
         public function common($pid = 0, &$result = array())//用于处理分类的公共方法
        {
            $res = $this->dbclass->where(['pid' => $pid])->column(['name', 'id', 'pid', 'path']);
            foreach ($res as $v) {
                if ($v['pid'] != 0) {
                    $count = (count(explode(',', $v['path'])) - 1) * 2;
                    $name = str_repeat('&nbsp;&nbsp;&nbsp;', $count) . $v['name'];
                } else {
                    $name = $v['name'];
                }
                $data['name'] = $name;
                $data['id'] = $v['id'];
                $result[] = $data;
                $this->common($v['id'], $result);
            }
            return $result;
        }
        #定义按钮
        private function makeclassButton($id)   
        {
            return [
                '编辑' => [
                    'auth' => 'article/edit',
                    'href' => url('article/articleedit', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '删除' => [
                'auth' => 'article/articledel',
                'href' => "javascript:articledel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ],
                
            ];
        }

}
