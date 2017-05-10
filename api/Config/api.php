<?php
$msgFormat = <<<str
<pre>
    1、我是例子
    2、我也是例子
</pre>
str;

$apiConfig = [
    //是否开启文档自动解析
    'annotation_to_doc' => 'lookup_doc_key',//开启文档解析 查看的key为lookup_doc_key 。false为关闭
    'access_look_doc_ip_list' => [//允许查看文档的ip列表
        '127.0.0.1'
    ],

    'convention_desc' => [//其它约定
        '消息格式' => $msgFormat
    ],

    //生成接口文档时显示，建议在route.php中配置路由Route::post('api' ,'api/Bootstrap/run');//即只允许post访问
    'api_url' => \Cml\Http\Request::baseUrl() . \Cml\Http\Response::url('api/Bootstrap/run', false),
    'doc_name' => '测试接口文档', //生成接口文档时做为标题

    //key  来源=>key
    'key' => [
        'web' => 'lxln-33k545xc00-354.xb-3dfgd',
        'android' => 'eo,ngn00eo-5930.b..sdeo-3n.gls03x',
        'ios' => '4j90f3jt-343fjdjf-l93nd'
    ],
    'api_log_on_redis_list_name' => false,//'api-test-log', //当default_cache为Redis时，会自动记下api的log。这边填写队列名称
    //请求有效时间
    'token_expire' => [
        'web' => 0,//不超时
        'android' => 300,//5min秒超时
        'ios' => 300//5min秒超时
    ],
    'use_nonce' => false,//是否验证nonce
    //ip限制
    'ip_deny' => [
        'web' => [//只允许某些ip访问

        ]
    ],
    //白名单 只允许访问白名单内的api
    'white_list' => [
        'android' => [
            'V1' => [
                '1-1',
            ]
        ]
    ],

    //黑名单
    'black_list' => [
        'ios' => [
            'V1' => [
                '1-2'
            ]
        ],
    ],
    //线上版本
    'version' => [
        'V1' => [//参数对应方法名
            //用户相关
            '1-1' => 'api\Controller\V1\TestController\register', //用户注册
            '1-2' => 'api\Controller\V1\TestController\login', //用户登录
        ],
        //'2.0'
        'V2' => [
            //参数对应方法名
        ],
    ],
    //自定义返回code说明 ，自动生成文档时显示
    'code' => require __DIR__ . DIRECTORY_SEPARATOR . 'code.php'
];

//包含上级环境中的配置并合并
$globalApiConfig = \Cml\Cml::getApplicationDir('global_config_path') . DIRECTORY_SEPARATOR
    . \Cml\Config::$isLocal . DIRECTORY_SEPARATOR . 'api' . '.php';

if (is_file($globalApiConfig)) {
    $apiConfig = array_merge($apiConfig, \Cml\Cml::requireFile($globalApiConfig));
}
return $apiConfig;
