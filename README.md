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

一，redis 使用介绍


use guanghua\redis\guanghua;

// 设置指定 key 的值
guanghua::redis()->SET('KEY_NAME', "VALUE");

// 获取指定 key 的值。
guanghua::redis()->GET('KEY_NAME');


// 向有序集合添加一个或多个成员
guanghua::redis()->ZADD('myset', 1, "hello");

guanghua::redis()->ZADD('myset', 5, "hello2");

// 返回有序集中，成员的分数值

$value = guanghua::redis()->ZRANGE('myset',0, -1,'WITHSCORES');

更多命令参考redis文档.............

二，redis cache 使用介绍

use guanghua\redis\guanghua;
// 缓存一个对象,或数组 200秒
guanghua::cache()->set('obj',new \queue\KeyWordJob(['id'=>['dwdwfefe']]),200);

guanghua::cache()->set('obj',[1,2,3,4,5],200);

// 获取缓存值
guanghua::cache()->get('obj');

//从缓存中删除所有值
guanghua::cache()->flush();

二，redis session 使用介绍
guanghua::session()->set('name',[1,2,3,4,5]);

// 获取缓存值
guanghua::session()->get('name');
