<?php
class Tool{
    private static $_redis = null;
    public static function getRedis($config){
        if(!self::$_redis){
            $redis = new Redis();
            $redis->connect($config['host'], $config['port']);
            $redis->auth($config['password']);
            $redis->select($config['db']);
            self::$_redis = $redis;
        }
        return self::$_redis;
    }

    public static function sendToAll($server,$result){
        foreach ($server->connections as $fd) {
            if ($server->isEstablished($fd)) {
                $server->push($fd, json_encode($result));
            }
        }
    }
}
