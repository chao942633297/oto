<?php


return [
    'url_route_on' => true,
    'trace' => [
        'type' => 'html', // 支持 socket trace file
    ],
    //各模块公用配置
    'extra_config_list' => ['database', 'route', 'validate'],
    //临时关闭日志写入
    'log' => [
        'type' => 'File',
    ],

    'app_debug' => true,
    'default_filter' => ['strip_tags', 'htmlspecialchars'],


    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------
    'cache' =>  [
        // redis缓存
        'redis'   =>  [
            // 驱动方式
            'type'     => 'redis',
            'expire'   => 0,
            // 服务器地址
            'host'     => '121.42.251.110',
            'port'     => 6379,
            'expire'   => 0,
            // 服务器地址
            'host'     => '',
            //端口号
            'port'     => '',
            'password' => '',
            'select'   => 0,
            'timeout'  => 0,
            'persistent' => false,
            'prefix' => '',
        ],
    ],

    //加密串
    'salt' => 'wZPb~yxvA!ir38&Z',

    //备份数据地址
    'back_path' => APP_PATH .'../back/',

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
       // 'expire'             => 24*3600*15,
        //时间
        'prefix'         => 'runjia',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],
    'wechat'                 => [
        # 微信的公众平台的appid
        'appid'=>'wx1c1d4b02b28ba4f8',
        # 微信开放平台的的appid
        'open_appid'=>'',
        # 公众号的secret
        'secret'=>'aa5ce1d241e705d27f45991d3266797c',
        # 开放平台引用秘钥
        'open_secret'=>'',
        # 微信公众号商户平台付key
        'pay_key'=>'3a479e1f8c4ee6491922ee7016da2bbf',  //未修改
        # 微信开放商户平台key
        'open_pay_key'=>'',
        # 微信公众商户平台商户id
        'mchid' => '1363960602',
        # 微信开放平台商户id
        'open_mchid' => '',
        #通知回调地址
        'notify_url'=>'http://admin.oto178.com/home/not/wechatNotify',
    ],

];