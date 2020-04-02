<?php

namespace guanghua\redis;


class guanghua
{	
    //队列配置项
    public static $redisArray = [
        'redis'=>[
            'hostname' => '121.37.3.163', //连接主机
            'port'     => 6379, //连接端口
            'database' => 0 //连接数据库
        ],
        'cache' => [],
        'session' => [],
    ];

    //数组共享组件实例的id索引
    private static $_components = [];

    //当前redis实例
    public static function redis()
    {
    	return self::ensure('redis',RedisConn::class);
    }
    
    //当前redis缓存实例
    public static function cache()
    {
    	return self::ensure('cache',RedisCache::class);
    }

    //当前session缓存实例
    public static function session()
    {
        return self::ensure('session',RedisSession::class);
    }

    //得到一个实例化的类对象 并注册到共享组件
    public static function ensure($id ,$class){
       
        if (isset(self::$_components[$id])) {
            
            return self::$_components[$id];
        }
        if (isset(static::$redisArray[$id])) {

            return self::$_components[$id] = new $class(static::$redisArray[$id]);
        }
        throw new Exception($id .' NOT : ' . get_class($this) . '::' . $class);
    }
}
?>
