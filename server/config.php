<?php
$config = [
    //websoket监听端口
    'ws_port' => 8282,
    //tcp监听端口
    'tcp_port' => 8283,
    //redis配置
    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
        'password' => '123456',
        'db' => 5,
    ],
];
return $config;