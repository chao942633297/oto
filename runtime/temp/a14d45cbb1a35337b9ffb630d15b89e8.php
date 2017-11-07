<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:72:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\data\index.html";i:1508312056;s:75:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\public\header.html";i:1508313446;s:75:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\public\footer.html";i:1508312213;}*/ ?>
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
    <!-- Panel Other -->
    <div class="ibox float-e-margins">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>数据表列表</h5>
            </div>
            <div class="ibox-content">

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>表名</th>
                        <th>记录数</th>
                        <th>上次备份时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($tables)): if(is_array($tables) || $tables instanceof \think\Collection || $tables instanceof \think\Paginator): if( count($tables)==0 ) : echo "" ;else: foreach($tables as $key=>$vo): ?>
                        <tr>
                            <td><?php echo $vo["Tables_in_".config('database')['database']]; ?></td>
                            <td><?php echo $vo['alls']; ?></td>
                            <td><?php echo $vo['ctime']; ?></td>
                            <td><?php echo $vo['operate']; ?></td>
                        </tr>
                    <?php endforeach; endif; else: echo "" ;endif; endif; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
<!-- End Panel Other -->
</div>
<!-- 角色分配 -->
<div class="col-sm-12" style="display: none" id="wait">
    <div class="ibox ">
        <div class="ibox-content">
            <div class="spiner-example">
                <div class="sk-spinner sk-spinner-three-bounce">
                    <div class="sk-bounce1"></div>
                    <div class="sk-bounce2"></div>
                    <div class="sk-bounce3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="__JS__/jquery.min.js?v=2.1.4"></script>
<script src="__JS__/bootstrap.min.js?v=3.3.6"></script>
<script src="__JS__/content.min.js?v=1.0.0"></script>
<script src="__JS__/plugins/bootstrap-table/bootstrap-table.min.js"></script>
<script src="__JS__/plugins/bootstrap-table/bootstrap-table-mobile.min.js"></script>
<script src="__JS__/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="__JS__/plugins/suggest/bootstrap-suggest.min.js"></script>
<script src="__JS__/plugins/layer/laydate/laydate.js"></script>
<script src="__JS__/plugins/sweetalert/sweetalert.min.js"></script>
<script src="__JS__/plugins/layer/layer.min.js"></script>
<script src="__JS__/jquery.form.js"></script>
</body>
</html>
<script type="text/javascript">
    function importData(table, all){
        index = layer.open({
            type: 1,
            area:'400px',
            title:'正在操作',
            skin: 'layui-layer-demo', //加上边框
            content: $('#wait')
        });
        $.getJSON("<?php echo url('data/importData'); ?>", {'table' : table}, function(res){
            layer.close(index);
            layer.alert('备份成功！', {icon: 1}, function(){
                window.location.reload();
            });
        })
    }

    function backData(table){
        index = layer.open({
            type: 1,
            area:'400px',
            title:'正在操作',
            skin: 'layui-layer-demo', //加上边框
            content: $('#wait')
        });
        $.getJSON("<?php echo url('data/backData'); ?>", {'table' : table}, function(res){
            layer.close(index);
            if(res.code == 1){
                layer.alert('还原成功！', {icon: 1}, function(){
                    window.location.reload();
                });
            }else{
                layer.alert(res.msg, {icon: 2});
            }
        })
    }
</script>
