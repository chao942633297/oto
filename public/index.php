<?php
header("Access-Control-Allow-Credentials:true");
$allow_origin = array(  
    'http://www.oto178.com',
    'http://admin.oto178.com',
);
$origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';

if(in_array($origin, $allow_origin)){  
    header('Access-Control-Allow-Origin:'.$origin);       
}
// [ 应用入口文件 ]
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 定义应用缓存目录
define('RUNTIME_PATH', __DIR__ . '/../runtime/');
// 开启调试模式
define('APP_DEBUG', true);
// 加载框架引导文件
define('WEB_URL', "http://www.oto178.cn");
define('ADMIN_URL', "http://admin.oto178.com");
require __DIR__ . '/../thinkphp/start.php';


