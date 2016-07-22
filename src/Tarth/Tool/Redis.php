<?php
namespace Tarth\Tool;

class Redis {
    private static $_connections;
    private static $_cacheServer = "127.0.0.1:6379";
    private static $_queueServer = "127.0.0.1:6379";

    private static function _getConnection($server) {
        if (!empty(self::$_connections[$server])) {
            return self::$_connections[$server];
        }

        $connection = new \Redis();

        try {
            //Fix 'Redis::connect(): php_network_getaddresses: getaddrinfo failed: Name or service not known' in docker php
            list($ip, $port) = explode(':', $server);
            $connection->connect($ip, $port);
            //$connection->connect($server);
            $connection->server = $server;

            self::$_connections[$server] = $connection;
        } catch (Exception $e) {
            \Tarth\Tool\Log::logger()->addError($e->getMessage());
        }

        return $connection;
    }

    static public function setCacheServer($server) {
        self::$_cacheServer = $server;
    }

    static public function setQueueServer($server) {
        self::$_queueServer = $server;
    }   

    /**
     * 缓存Redis
     * @return [type] [description]
     */
    static public function cacheRedis() {
        return self::_getConnection(self::$_cacheServer);
    }

    /**
     * 队列Redis
     * @return [type] [description]
     */
    static public function queueRedis() {
        return self::_getConnection(self::$_queueServer);
    }

    /**
     * 处理redis错误
     * @param  Redis $redis  Redis对象
     * @param  RedisException $e  错误信息
     * @return [type]        [description]
     */
    static public function handlerRedisException($redis, RedisException $e) {
        if (isset($redis->server)) {
            unset(self::$_connections[$redis->server]);
        }
    }
}