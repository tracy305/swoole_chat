<?php
$client = new swoole_client(SWOOLE_SOCK_TCP);

//连接到服务器
if (!$client->connect('127.0.0.1', 8283)){
    die("connect failed.");
}
//向服务器发送数据
$msg = isset($_GET['msg']) ? $_GET['msg'] : '这是一个系统消息';

if (!$client->send($msg)) {
    die("send failed.");
}
//从服务器接收数据
$data = $client->recv();
if (!$data) {
    die("recv failed.");
}
echo $data;
//关闭连接
$client->close();