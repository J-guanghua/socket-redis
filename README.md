Installation
------------

安装此扩展的首选方法是通过 [composer](http://getcomposer.org/download/).

要么跑

```
php composer.phar require guanghua/socket-redis "dev-master"
```

或添加

```
"guanghua/socket-redis": "dev-master"
```

到你的需求部分 `composer.json` 文件.


Usage
-----

一旦安装了扩展，只需使用它在您的代码:

```php

一，redis 使用操作


use guanghua\redis\RedisConn;

$redis = RedisConn([
   'hostname' => '127.0.0.1', //连接主机
   'port'     => 6379, //连接端口
   'database' => 0 //连接数据库
])

// 设置指定 key 的值
$redis->SET('KEY_NAME', "VALUE");

// 获取指定 key 的值。
$redis->GET('KEY_NAME');


// 向有序集合添加一个或多个成员
$redis->ZADD('myset', 1, "hello");

$redis->ZADD('myset', 5, "hello2");

// 返回有序集中，成员的分数值
$value = $redis->ZRANGE('myset',0, -1,'WITHSCORES');

更多命令参考redis文档.............

二，redis cache 使用操作

use guanghua\redis\RedisCache;

$cache = RedisCache([
   'redis' => $redis,
])
// 缓存一个对象,或数组 200秒
$cache->set('obj',new \queue\KeyWordJob(['id'=>['dwdwfefe']]),200);

$cache->set('obj',[1,2,3,4,5],200);

// 获取缓存值
$value = $cache->get('obj');

//从缓存中删除所有值
$cache->flush();

三，redis session 使用操作

use guanghua\redis\RedisSession;

// session实例,可以解决分布式系统用户登录 
$session = RedisSession([
   'redis' => $redis,
])

//设置 session 值
$session->set('username',[1,2,3,4,5]);

// 方式2 设置session变量
$_SESSION['username'] = 'JohnDoe';
$_SESSION['email'] = 'john@example.com';
 
//获取 session 值
$session->get('username');
