<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:75:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\carousel\edit.html";i:1508320884;s:75:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\public\header.html";i:1508313446;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico">
    <link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="__CSS__/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="__CSS__/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="__CSS__/animate.min.css" rel="stylesheet">
    <link href="__CSS__/style.min.css?v=4.1.0" rel="stylesheet">
    <link href="__CSS__/plugins/sweetalert/sweetalert.css" rel="stylesheet">
</head>

<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>编辑轮播</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="dropdown-toggle" data-toggle="dropdown" href="form_basic.html#">
                            <i class="fa fa-wrench"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal m-t" method="post" enctype="multipart/form-data" action="<?php echo url('carousel/do_insert'); ?>">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">名称：</label>
                            <div class="input-group col-sm-4">
                                <input id="title" type="text" class="form-control" name="name" required=""
                                       aria-required="true" value="<?php echo $data['name']; ?>">
                            </div>
                        </div>                       
                        <div class="form-group">
                            <label class="col-sm-3 control-label">图片：</label>
                            <div class="input-group col-sm-4">
                                <img src="<?php echo $data['pic']; ?>"  width="50" height="50">
                                <input  type="file"  name="pic" id="pic"/>                               
                            </div>
                        </div>                          
                        <div class="form-group draggable ui-draggable">
                            <label class="col-sm-3 control-label">链接：</label>
                            <div class="col-sm-4 input-group">
                                <input id="title" type="text" class="form-control" name="url" required=""
                                       aria-required="true" value="<?php echo $data['url']; ?>">                                 
                            </div>
                        </div>                                               
                        <div class="form-group">
                            <div class="col-sm-4 col-sm-offset-3">
                                <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                <input type="hidden" name="dosubmit" value="1">
                                <button class="btn btn-primary" type="submit">提交</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico">
    <link href="__CSS__/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="__CSS__/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="__CSS__/plugins/bootstrap-table/bootstrap-table.min.css" rel="stylesheet">
    <link href="__CSS__/animate.min.css" rel="stylesheet">
    <link href="__CSS__/style.min.css?v=4.1.0" rel="stylesheet">
    <link href="__CSS__/plugins/sweetalert/sweetalert.css" rel="stylesheet">
</head>

