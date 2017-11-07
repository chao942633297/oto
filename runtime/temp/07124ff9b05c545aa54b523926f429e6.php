<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:73:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\index\index.html";i:1508312532;s:75:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\public\header.html";i:1508313446;s:75:"C:\xampp\htdocs\tpadmin\public/../application/admin\view\public\footer.html";i:1508312213;}*/ ?>
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
<div class="col-sm-6" style="margin-top: 20px;">
    <div id="money" style="width: 710px;height:400px;"></div>
</div>
<div class="col-sm-6" style="margin-top: 20px;">
    <div id="userType" style="width: 710px;height:400px;"></div>
</div>
<div class="col-sm-6" style="margin-top: 20px;">
    <div id="payUser" style="width: 710px;height:400px;"></div>
</div>
<div class="col-sm-6" style="margin-top: 20px;">
    <div id="orderDay" style="width: 710px;height:400px;"></div>
</div>
<div class="col-sm-6" style="margin-top: 20px;">
    <div id="moneyType" style="height:400px;"></div>
</div>
<!-- <div class="col-sm-6" style="margin-top: 20px;">
    <div id="" style="height:400px;"></div>
</div> -->
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
<script src="__JS__/echarts.js"></script>
</body>
</html>
<script>
//收入/支出
var money = echarts.init(document.getElementById('money'));
option = {
    title : {
        text: '收入/支出列表',
        subtext: '人民币/元'
    },
    tooltip : {
        trigger: 'axis'
    },
    legend: {
        data:['收入金额','支出金额']
    },
    toolbox: {
        show : true,
        feature : {
            mark : {show: true},
            dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']},
            restore : {show: true},
            saveAsImage : {show: true}
        }
    },
    calculable : true,
    xAxis : [
        {
            type : 'category',
            data : ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月']
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    //数据    
    series : [
        {
            name:'收入金额',
            type:'bar',
            data:[1,2,3,4,5,6,7,8,9,10,11,12],
            //颜色
            itemStyle:{
                normal:{color:'#18a689'}
            },
            markPoint : {
                data : [
                    {type : 'max', name: '最大值'},
                    {type : 'min', name: '最小值'}
                ]
            },
            markLine : {
                data : [
                    {type : 'average', name: '平均值'}
                ]
            }
        },
        {
            name:'支出金额',
            type:'bar',
            //数组数据--12个元素
            data:[1,2,3,4,5,6,7,8,9,10,11,12],
            //颜色
            itemStyle:{
                normal:{color:'#ff3333'}
            },
            markPoint : {
                data : [
                    {type : 'max', name: '最大值'},
                    {type : 'min', name: '最小值'}
                ]
            },
            markLine : {
                data : [
                    {type : 'average', name : '平均值'}
                ]
            }
        }
    ]
};                  
money.setOption(option);
//用户类别所占比例
var user = echarts.init(document.getElementById('userType'));
options = {
    title : {
        text: '用户类别所占总数量比例',
        subtext: '平台用户数量:{15 + 10 + 5}人',
        x:'center'
    },
    tooltip : {
        trigger: 'item',
        formatter: "{a} <br/>{b} : {c} ({d}%)"
    },
    legend: {
        orient : 'vertical',
        x : 'left',
        data:['店主','维修技师','普通会员']
    },
    toolbox: {
        show : true,
        feature : {
            mark : {show: true},
            dataView : {show: true, readOnly: false},
            magicType : {
                show: true, 
                type: ['pie', 'funnel'],
                option: {
                    funnel: {
                        x: '25%',
                        width: '50%',
                        funnelAlign: 'left',
                        max: 1548
                    }
                }
            },
            restore : {show: true},
            saveAsImage : {show: true}
        }
    },
    calculable : true,
    series : [
        {
            name:'用户类别所占比例',
            type:'pie',
            radius : '55%',
            center: ['50%', '60%'],
            data:[
                {value:15, name:'店主'},
                {value:10, name:'维修技师'},
                {value:5, name:'普通会员'}
            ]
        }
    ],
    color: ['rgb(230,50,50)','rgb(50,230,50)','rgb(50,50,200)']
};                 
user.setOption(options);

//是否消费会员所占比例
var payuser = echarts.init(document.getElementById('payUser'));
optionss = {
    title : {
        text: '消费用户所占比例',
        subtext: '平台用户数量:{20 + 10}人',
        x:'center'
    },
    tooltip : {
        trigger: 'item',
        formatter: "{a} <br/>{b} : {c} ({d}%)"
    },
    legend: {
        orient : 'vertical',
        x : 'left',
        data:['消费会员','未消费会员']
    },
    toolbox: {
        show : true,
        feature : {
            mark : {show: true},
            dataView : {show: true, readOnly: false},
            magicType : {
                show: true, 
                type: ['pie', 'funnel'],
                option: {
                    funnel: {
                        x: '25%',
                        width: '50%',
                        funnelAlign: 'left',
                        max: 1548
                    }
                }
            },
            restore : {show: true},
            saveAsImage : {show: true}
        }
    },
    calculable : true,
    series : [
        {
            name:'用户类别所占比例',
            type:'pie',
            radius : '55%',
            center: ['50%', '60%'],
            data:[
                {value:20, name:'消费会员'},
                {value:10, name:'未消费会员'}
            ]
        }
    ],
    color: ['rgb(230,50,50)','rgb(50,230,50)']
};                 
payuser.setOption(optionss);

var order = echarts.init(document.getElementById('orderDay'));

optionsss = {
    title : {
        text: '一周内订单量走势',
        subtext: '订单量/个'
    },
    tooltip : {
        trigger: 'axis'
    },
    legend: {
        data:['最高下单数量','最低下单数量']
    },
    toolbox: {
        show : true,
        feature : {
            mark : {show: true},
            dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']},
            restore : {show: true},
            saveAsImage : {show: true}
        }
    },
    calculable : true,
    xAxis : [
        {
            type : 'category',
            boundaryGap : false,
            data : ['周一','周二','周三','周四','周五','周六','周日']
        }
    ],
    yAxis : [
        {
            type : 'value',
        }
    ],
    series : [
        {
            name:'下单数量',
            type:'line',
            data:[1,2,3,4,5,6,7],
            //颜色
            itemStyle:{
                normal:{color:'#18a689'}
            },
            markPoint : {
                data : [
                    {type : 'max', name: '最大值'},
                    {type : 'min', name: '最小值'}
                ]
            },
            markLine : {
                data : [
                    {type : 'average', name: '平均值'}
                ]
            }
        },
    ]
};
order.setOption(optionsss);

// var moneytype = echarts.init(document.getElementById('moneyType'));
// option4s = {
//     tooltip: {
//         trigger: 'item',
//         formatter: "{a} <br/>{b}: {c} ({d}%)"
//     },
//     legend: {
//         orient: 'vertical',
//         x: 'left',
//         data:['直接访问','邮件营销','联盟广告','视频广告','搜索引擎']
//     },
//     series: [
//         {
//             name:'金额类别所占比例',
//             type:'pie',
//             radius: ['50%', '70%'],
//             avoidLabelOverlap: false,
//             label: {
//                 normal: {
//                     show: false,
//                     position: 'center'
//                 },
//                 emphasis: {
//                     show: true,
//                     textStyle: {
//                         fontSize: '30',
//                         fontWeight: 'bold'
//                     }
//                 }
//             },
//             labelLine: {
//                 normal: {
//                     show: false
//                 }
//             },
//             data:[
//                 {value:335, name:'直接访问'},
//                 {value:310, name:'邮件营销'},
//                 {value:234, name:'联盟广告'},
//                 {value:135, name:'视频广告'},
//                 {value:1548, name:'搜索引擎'}
//             ]
//         }
//     ]
// };
// moneytype.setOption(option4s);

</script>