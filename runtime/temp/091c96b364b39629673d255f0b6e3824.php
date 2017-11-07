<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:74:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\config\index.html";i:1508319700;s:75:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\public\header.html";i:1508313446;s:75:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\public\footer.html";i:1508312213;}*/ ?>
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
                <h5>配置列表</h5>
            </div>
            <div class="ibox-content">

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>序号</th>
                        <th>描述</th>
                        <th>配置参数</th>
                        <th>上次修改时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($config)): if(is_array($config) || $config instanceof \think\Collection || $config instanceof \think\Paginator): if( count($config)==0 ) : echo "" ;else: foreach($config as $k=>$vo): ?>
                        <tr>
                            <td><?php echo $k; ?></td>
                            <td><?php echo $vo['description']; ?></td>
                            <td><?php echo $vo['value']; ?></td>
                            <td><?php echo date('Y-m-d H:i:s',$vo['updated_at'] ); ?></td>
                            <td><?php echo $operate[$k]['operate']; ?></td>
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
<div class='table table-bordered' style="display: none;" id="wait">
    <form method="post" style="margin-top: 15px;" id="myform">
        <div class="form-group">
            <label class="control-label col-sm-4" style="height: 34px;line-height: 34px">参数：</label>
            <div class="input-group col-sm-6">

                <input id="value_num" type="number" data-id="" class="form-control" name="value_num" required=""
                       aria-required="true" value="" >
            </div>
        </div>                     
        <div class="form-group" >
            <div class="col-sm-10 col-sm-offset-2">
                <button class="btn btn-primary " type="button" style="float: right;margin:15px auto" id="biaoshi">提交</button>
            </div>
        </div>
    </form>
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

    function edit(id){

       $.getJSON("<?php echo url('config/edit'); ?>", {'id' : id,type:1}, function(res){
            $('#value_num').val(res.data);
            $('#value_num').data('id',id);

        }) 
        layer.open({
            type: 1,
            area:'400px',
            title:'正在操作',
            skin: 'layui-layer-demo', //加上边框
            content: $('#wait')
        });

    }
   $('#biaoshi').on('click',function(){
        layer.closeAll();
        var value = $('#value_num').val();
        var id = $('#value_num').data('id');
        $.ajax({
            type: "GET",
            url: "./edit",
            data: {value:value,type:2,id:id},
            dataType: "json",
            success: function(res){
                if (res.status == 1) {
                    layer.alert(res.msg, {title: '友情提示', icon: 1, closeBtn: 0}, function(){
                        window.location.href = res.data;
                    });
                }else{
                    layer.alert(res.msg, {title: '友情提示', icon: 1, closeBtn: 0}, function(){
                        
                    });
                }
                
            }
        });
   });
</script>