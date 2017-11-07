<?php
// ini_set('session.cookie_domain', '.runjiaby.com');
// header('Access-Control-Allow-Credentials: true');
// header("Access-Control-Allow-Origin: http://h.runjiaby.com"); // 允许h.runjiaby.com发起的跨域请求  
// header('Access-Control-Allow-Methods:POST,GET'); 
// header('Access-Control-Allow-Headers:x-requested-with,content-type');
// header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');  
// [ 应用入口文件 ]
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 定义应用缓存目录
define('RUNTIME_PATH', __DIR__ . '/../runtime/');
// 开启调试模式
define('APP_DEBUG', true);
// 加载框架引导文件

require __DIR__ . '/../thinkphp/start.php';


