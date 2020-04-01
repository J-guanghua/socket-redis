<?php

namespace guanghua\redis;


class guanghua
{	
	const REDIS_NAME = 'redis'; // 共享组件 redis
	const CACHE_NAME = 'cache'; //共享组件 cache
    const SESSION_NAME = 'session'; //共享组件 session
	
	//redis连接配置
	const HOST_LINK = [
		'hostname' => '121.37.3.163', //连接主机
		'port'     => 6379, //连接端口
		'database' => 0 //连接数据库
    ];

    //数组共享组件实例的id索引
    private static $_components = [];

    //当前redis实例
    public static function redis()
    {
    	return self::ensure(guanghua::REDIS_NAME,RedisConn::class,guanghua::HOST_LINK);
    }
    
    //当前redis缓存实例
    public static function cache()
    {
    	return self::ensure(guanghua::CACHE_NAME,RedisCache::class,[]);
    }

    //当前session缓存实例
    public static function session()
    {
        return self::ensure(guanghua::SESSION_NAME,RedisSession::class,[]);
    }

    //得到一个实例化的类对象 并注册到共享组件
    public static function ensure($id ,$class ,$array = []){
       
        if (isset(self::$_components[$id])) {
            
            return self::$_components[$id];
        }
        return self::$_components[$id] = new $class($array);
    }
}
?>
