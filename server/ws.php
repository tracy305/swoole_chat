<?php
ini_set('date.timezone','Asia/Shanghai');
require_once 'Tool.php';

$config = require_once 'config.php';
$redis = Tool::getRedis($config['redis']);

//启动的时候清空redis内容，否则在线用户等信息会不准
$redis->flushDB();

//创建一个WebSocket服务器
$server = new Swoole\WebSocket\Server("0.0.0.0", $config['ws_port']);

//多监听一个tcp的端口，用于接收Swoole\Client发来的推送消息
$port1 = $server->listen("0.0.0.0", $config['tcp_port'], SWOOLE_SOCK_TCP);
$port1->set(['open_websocket_protocol' => false]);

//WebSocket连接成功
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    //echo "server: handshake success with fd{$request->fd}\n";
});

//WebSocket收到消息
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) use ($redis){
    $data = json_decode($frame->data,true);
    $name = isset($data['name']) ? $data['name'] : '';
    $action = isset($data['action']) ? $data['action'] : '';
    if($action == 'connect'){
        //客户端连接
        if($name){
            //关联客户端和用户名
            $redis->set('fd'.$frame->fd,$name);
            //存储当前用户
            $redis->sadd('online_list',$name);
            //获取当前在线用户
            $users = $redis->smembers('online_list');
            //获取消息列表
            $msg_list = $redis->lrange('msg_list',0,-1);
            //发送消息
            $result = [
                'class' => 'red',
                'msg' => "系统消息：{$name}进入聊天室\r\n",
                'count' => count($users),
                'list' => json_encode($msg_list),
                'list_to' => $name,
                'uids' => json_encode($users),
                'name' => $name,
            ];
            Tool::sendToAll($server,$result);
        }
    }else{
        // 客户端发言
        $name = $redis->get('fd'.$frame->fd);
        if($name){
            $msg = "{$name}(".date('Y-m-d H:i:s').")：{$data['message']}\r\n";
            $result = [
                'msg' => $msg,
                'name' => $name,
            ];
            Tool::sendToAll($server,$result);
            //存储消息
            $redis->rpush('msg_list',$msg);
        }
    }
});

//WebSocket连接关闭
$server->on('close', function ($ser, $fd) use ($redis){
    //echo "client {$fd} closed\n";   //后台运行时要去掉echo
    //删除当前客户端
    $name = $redis->get('fd'.$fd);
    $redis->srem('online_list',$name);
    //获取当前在线用户
    $users = $redis->smembers('online_list');
    //通知
    $result = [
        'class' => 'red',
        'msg' => "系统消息：{$name}离开聊天室\r\n",
        'count' => count($users),
        'uids' => json_encode($users),
        'name' => $name,
    ];
    foreach ($server->connections as $conn) {
		//不发送给当前退出的客户端
		if ($server->isEstablished($conn) && $conn != $fd) {
			$server->push($conn, json_encode($result));
		}
	}
});

//tcp连接成功
$port1->on('connect', function ($port1, $fd) {
    //echo "Client: Connect.\n";
});
//tcp收到消息
$port1->on('receive', function ($port1, $fd, $from_id, $data) use ($server) {
    //回复tcp客户端
    $port1->send($fd, "Server: ".$data);
    //推送websoket客户端
    $msg = "系统推送(".date('Y-m-d H:i:s').")：{$data}\r\n";
    $result = [
        'class' => 'green',
        'msg' => $msg,
    ];
    Tool::sendToAll($server,$result);
});

$server->start();