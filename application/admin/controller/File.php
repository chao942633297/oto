<?php
namespace app\admin\controller;
use think\Controller;
class File extends Controller
{
    #单图片上传
    public function upload()
    {
        $file = request()->file('img');
        
        if(isset($file)){
            // 获取表单上传文件 例如上传了001.jpg
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>500000,'ext'=>'jpg,jpeg,png,gif'])->move(ROOT_PATH . 'public' . DS .'uploads');
            if($info){
                      // 成功上传后 获取上传信息
                $a      =$info->getSaveName();
                $imgp   = str_replace("\\","/",$a);
                $imgpath='/uploads/'.$imgp;

                // $image = \think\Image::open('.'.$imgpath);
                // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                // $image -> thumb(600, 600) -> save('.'.$imgpath);//直接把缩略图覆盖原图

                return ['code' => 1, 'data' =>WAB_NAME.$imgpath, 'msg' =>'上传成功'];
            }else{
                // 上传失败获取错误信息
                return ['code' => 2, 'data' =>'', 'msg' =>$file->getError()];
            }
        }
    }

    #多图片上传
    public function upload_many()
    {    
        $Path = [];
        // 获取表单上传文件
        $files = request()->file('imgs');
        foreach($files as $file){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(['size'=>500000,'ext'=>'jpg,jpeg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                      // 成功上传后 获取上传信息
                $a      =$info->getSaveName();
                $imgp   = str_replace("\\","/",$a);
                $imgpath='/uploads/'.$imgp;

                // $image = \think\Image::open('.'.$imgpath);
                // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
                // $image -> thumb(600, 600) -> save('.'.$imgpath);//直接把缩略图覆盖原图
                $Path[] = WAB_NAME.$imgpath;
            }else{
                // 上传失败获取错误信息
                return ['code' => 2, 'data' =>'', 'msg' =>$file->getError()];
            }    
        }
        
        return ['code' => 1, 'data' =>$Path, 'msg' =>'上传成功'];

    }


    #导入excel表
    public function upload_excel()
    {
        $file = request()->file('excel');

        if ( isset($file) ) {

            $info = $file->validate(['ext'=>'xls,xlsx,csv'])->move(ROOT_PATH . 'public' . DS .'uploads' . DS . 'excel');

            // $info = $file->validate(['ext'=>'xls,xlsx,csv'])->move(ROOT_PATH.'public'.DS);
            if($info){
                // 成功上传后 获取上传信息
                #文件后缀
                $ext    =$info->getExtension();
                
                $a      =$info->getSaveName();  
                #文件名称
                $name   =$info->getFilename();

                $imgp   = str_replace("\\","/",$a);
                $imgpath='uploads/excel/'.$imgp;

                return ['code' => 1, 'data' =>['url'=>$imgpath,'ext'=>$ext], 'msg' =>'上传成功'];
            }else{
                // 上传失败获取错误信息
                return ['code' => 2, 'data' =>'', 'msg' =>$file->getError()];
            }            
        }
    }

   
}
