<?php

namespace guanghua\redis;

use Exception;

//用于操作redis命令
class RedisConn extends Monitor
{
	//事件在数据库连接建立后触发的事件
    const EVENT_AFTER_OPEN = 'afterOpen';

    //主机IP
    public $hostname = 'localhost';
    //端口
    public $port = 6379;
	//将unix套接字路径(例如' /var/run/redis/redis.sock ')用于连接到redis服务
    public $unixSocket;
    //连接密码
    public $password;
    //要使用的redis数据库的整数。这是从0开始的整数值。默认值为0。
    public $database = 0;
	
	//用于连接到redis的浮动超时
    public $connectionTimeout = null;
	//浮动超时使用的redis套接字时，读取和写入数据。如果没有设置，将使用php默认值。
    public $dataTimeout = null;
    //连接标志的选择仅限于“STREAM_CLIENT_CONNECT”(默认)、“STREAM_CLIENT_ASYNC_CONNECT”和“STREAM_CLIENT_PERSISTENT”
    public $socketClientFlags = STREAM_CLIENT_CONNECT;


    public $redisCommands = [
        'APPEND', // 向键追加一个值
        'AUTH', // 对服务器进行身份验证
        'BGREWRITEAOF', // 异步重写仅追加文件
        'BGSAVE', // 异步地将数据集保存到磁盘
        'BITCOUNT', // Count在字符串中设置位
        'BITFIELD', // 对字符串执行任意的位域整数操作
        'BITOP', // 在字符串之间执行按位操作
        'BITPOS', // 查找字符串中设置或清除的第一个位
        'BLPOP', // 删除并获取列表中的第一个元素，或阻塞，直到其中一个元素可用为止
        'BRPOP', // 删除并获取列表中的最后一个元素，或阻塞，直到其中一个元素可用为止
        'BRPOPLPUSH', // 从列表中取出一个值，将其推入另一个列表并返回;或阻塞，直到有一个可用
        'CLIENT KILL', // 终止客户端的连接
        'CLIENT LIST', // 获取客户端连接的列表
        'CLIENT GETNAME', // 获取当前连接名
        'CLIENT PAUSE', // 在一段时间内停止处理来自客户端的命令
        'CLIENT REPLY', // 指示服务器是否响应命令
        'CLIENT SETNAME', // 设置当前连接名
        'CLUSTER ADDSLOTS', // 向接收节点分配新的哈希槽
        'CLUSTER COUNTKEYSINSLOT', // 返回指定哈希槽中的本地键的数量
        'CLUSTER DELSLOTS', // 在接收节点中将哈希槽设置为未绑定
        'CLUSTER FAILOVER', // 强制从服务器执行其主服务器的手动故障转移。
        'CLUSTER FORGET', // 从nodes表中删除一个节点
        'CLUSTER GETKEYSINSLOT', // 返回指定哈希槽中的本地键名
        'CLUSTER INFO', // 提供有关Redis集群节点状态的信息
        'CLUSTER KEYSLOT', // 返回指定键的哈希槽
        'CLUSTER MEET', // 强制一个节点集群与另一个节点握手
        'CLUSTER NODES', // 获取节点的集群配置
        'CLUSTER REPLICATE', //将节点重新配置为指定主节点的从节点
        'CLUSTER RESET', //重置一个Redis集群节点
        'CLUSTER SAVECONFIG', // 强制节点将集群状态保存到磁盘上
        'CLUSTER SETSLOT', // 将哈希槽绑定到特定节点
        'CLUSTER SLAVES', // 列出指定主节点的从节点
        'CLUSTER SLOTS', // 获取集群槽到节点映射的数组
        'COMMAND', // 获取Redis命令详细信息的数组
        'COMMAND COUNT', // 获取Redis命令的总数
        'COMMAND GETKEYS', // 提取键给予一个完整的Redis命令
        'COMMAND INFO', // 获取特定的Redis命令细节数组
        'CONFIG GET', // 获取配置参数的值
        'CONFIG REWRITE', // 使用内存中的配置重新编写配置文件
        'CONFIG SET', // 将配置参数设置为给定值
        'CONFIG RESETSTAT', // 重置信息返回的状态
        'DBSIZE', // 返回所选数据库中的键数
        'DEBUG OBJECT', // 获取有关密钥的调试信息
        'DEBUG SEGFAULT', // 使服务器崩溃
        'DECR', // 将键的整数值减一
        'DECRBY', // 按给定数字递减键的整数值
        'DEL', // 删除一个关键
        'DISCARD', // 丢弃在MULTI之后发出的所有命令
        'DUMP', // 返回存储在指定键上的值的序列化版本
        'ECHO', // 返回给定字符串
        'EVAL', // 执行Lua脚本服务器端
        'EVALSHA', // 执行Lua脚本服务器端
        'EXEC', // 执行多个命令后发出的所有命令
        'EXISTS', // 确定是否存在密钥
        'EXPIRE', // 设置密钥的时间以秒为单位
        'EXPIREAT', // 将密钥的过期时间设置为UNIX时间戳
        'FLUSHALL', // 从所有数据库中删除所有键
        'FLUSHDB', // 从当前数据库中删除所有键
        'GEOADD', // 在使用已排序集表示的地理空间索引中添加一个或多个地理空间项
        'GEOHASH', // 将地理空间索引的成员作为标准的geohash字符串返回
        'GEOPOS', // 返回地理空间索引成员的经度和纬度
        'GEODIST', // 返回地理空间索引的两个成员之间的距离
        'GEORADIUS', // 查询表示地理空间索引的已排序集，以获取与给定的最大距离相匹配的成员
        'GEORADIUSBYMEMBER', // 查询表示地理空间索引的已排序集，以获取与成员匹配的给定最大距离的成员
        'GET', // 获取密钥的值
        'GETBIT', // 返回键处存储的字符串值中偏移的位值
        'GETRANGE', // 获取存储在键上的字符串的子字符串
        'GETSET', // 设置键的字符串值并返回其旧值
        'HDEL', // 删除一个或多个哈希字段
        'HEXISTS', // 确定是否存在哈希字段
        'HGET', // 获取哈希字段的值
        'HGETALL', // 获取散列中的所有字段和值
        'HINCRBY', // 将哈希字段的整数值增加给定的数字
        'HINCRBYFLOAT', // 将哈希字段的浮点值增加给定的值
        'HKEYS', // 在散列中获取所有字段
        'HLEN', // 获取散列中的字段数
        'HMGET', // 获取所有给定哈希字段的值
        'HMSET', // 将多个哈希字段设置为多个值
        'HSET', // 设置哈希字段的字符串值
        'HSETNX', // 仅当哈希字段不存在时，才设置该字段的值
        'HSTRLEN', // 获取哈希字段值的长度
        'HVALS', // 获取散列中的所有值
        'INCR', // 将键的整数值增加1
        'INCRBY', // 将键的整数值增加给定的值
        'INCRBYFLOAT', // 将键的浮点值增加给定的值
        'INFO', // 获取关于服务器的信息和统计信息
        'KEYS', // 查找与给定模式匹配的所有键
        'LASTSAVE', // 获取最后一次成功保存到磁盘的UNIX时间戳
        'LINDEX', // 通过元素的索引从列表中获取元素
        'LINSERT', // 在列表中的另一个元素之前或之后插入一个元素
        'LLEN', // 获取列表的长度
        'LPOP', // 删除并获取列表中的第一个元素
        'LPUSH', // 在列表前添加一个或多个值
        'LPUSHX', // 仅当列表存在时，才将值添加到列表中
        'LRANGE', // 从列表中获取元素的范围
        'LREM', // 从列表中删除元素
        'LSET', // 根据列表中的元素的索引设置其值
        'LTRIM', // 将列表修剪到指定范围
        'MGET', // 获取所有给定键的值
        'MIGRATE', // 自动地将一个键从一个Redis实例转移到另一个Redis实例
        'MONITOR', //实时监听服务器接收到的所有请求
        'MOVE', // 将密钥移动到另一个数据库
        'MSET', // 将多个键设置为多个值
        'MSETNX', //只有在没有键存在的情况下，才将多个键设置为多个值
        'MULTI', // 标记事务块的开始
        'OBJECT', // 检查Redis对象的内部机制
        'PERSIST', // 从密钥中移除过期
        'PEXPIRE', // 设置键的时间(以毫秒为单位)
        'PEXPIREAT', // 将密钥的过期时间设置为UNIX时间戳(以毫秒为单位)
        'PFADD', // 将指定的元素添加到指定的HyperLogLog。
        'PFCOUNT', // 返回由HyperLogLog at key观察到的集合的近似基数
        'PFMERGE', // 将N个不同的HyperLogLogs合并为一个。
        'PING', // Ping服务器
        'PSETEX', // 设置键的值和过期时间(以毫秒为单位)
        'PSUBSCRIBE', // 侦听发布到与给定模式匹配的通道的消息
        'PUBSUB', // 检查发布/订阅子系统的状态
        'PTTL', // 获取密钥的生存时间(以毫秒为单位)
        'PUBLISH', // 向通道发送消息
        'PUNSUBSCRIBE', // 停止监听发送到与给定模式匹配的通道的消息
        'QUIT', // 关闭连接
        'RANDOMKEY', // 从密钥空间返回一个随机密钥
        'READONLY', // 启用指向群集从节点的连接的读查询
        'READWRITE', // 禁用指向群集从节点的连接的读查询
        'RENAME', // 重命名一个关键
        'RENAMENX', // 仅在新密钥不存在时重命名密钥
        'RESTORE', // 使用提供的序列化值创建密钥，该值以前使用DUMP获得。
        'ROLE', // 在复制的上下文中返回实例的角色
        'RPOP', // 删除并获取列表中的最后一个元素
        'RPOPLPUSH', // 删除列表中的最后一个元素，将其添加到另一个列表中并返回
        'RPUSH', // 向列表追加一个或多个值
        'RPUSHX', // 仅当列表存在时，才向列表追加一个值
        'SADD', // 向一个集合添加一个或多个成员
        'SAVE', // 同步地将数据集保存到磁盘
        'SCARD', // 获取集合中的成员数
        'SCRIPT DEBUG', // 为执行的脚本设置调试模式。
        'SCRIPT EXISTS', // 检查脚本缓存中是否存在脚本。
        'SCRIPT FLUSH', // 从脚本缓存中删除所有脚本。
        'SCRIPT KILL', // 杀死当前正在执行的脚本。
        'SCRIPT LOAD', // 将指定的Lua脚本加载到脚本缓存中
        'SDIFF', // 减去多组
        'SDIFFSTORE', // 减去多个集合并将结果集存储在一个键中
        'SELECT', // 更改当前连接的选定数据库
        'SET', // 设置键的字符串值
        'SETBIT', // 设置或清除键处存储的字符串值的偏移位
        'SETEX', // 设置密钥的值和过期时间
        'SETNX', // 仅当键不存在时，才设置键的值
        'SETRANGE', // 从指定的偏移量开始，在键处覆盖字符串的一部分
        'SHUTDOWN', // 同步地将数据集保存到磁盘，然后关闭服务器
        'SINTER', // 相交多组
        'SINTERSTORE', // 与多个集合相交，并将结果集存储在一个键中
        'SISMEMBER', // 确定给定的值是否是集合的成员
        'SLAVEOF', // 使服务器成为另一个实例的奴隶，或将其提升为主实例
        'SLOWLOG', // 管理Redis慢速查询日志
        'SMEMBERS', // 把所有的成员放在一个集合里
        'SMOVE', // 将一个成员从一个集合移动到另一个集合
        'SORT', // 对列表中的元素进行排序，集合或已排序的集合
        'SPOP', // 从一个集合中删除并返回一个或多个随机成员
        'SRANDMEMBER', // 从一个集合中获取一个或多个随机成员
        'SREM', // 从集合中删除一个或多个成员
        'STRLEN', //获取存储在键中的值的长度
        'SUBSCRIBE', // 侦听发布到给定通道的消息
        'SUNION', // 添加多个集
        'SUNIONSTORE', // 添加多个集合并将结果集存储在一个键中
        'SWAPDB', // 交换两个Redis数据库
        'SYNC', // 用于复制的内部命令
        'TIME', // 返回当前服务器时间
        'TOUCH', // 更改密钥的最后访问时间。返回指定的现有键的数目。
        'TTL', // 要有时间为钥匙而活
        'TYPE', // 确定键处存储的类型
        'UNSUBSCRIBE', // 停止监听发送到指定频道的消息
        'UNLINK', // 在另一个线程中异步删除一个键。否则它和DEL一样，但是没有阻塞。
        'UNWATCH', // Forget about all watched keys
        'WAIT', // 等待在当前连接上下文中发送的所有写命令的同步复制
        'WATCH', // 观察给定的键来确定MULTI/EXEC块的执行
        'ZADD', // 将一个或多个成员添加到已排序的集合中，或更新其分数(如果它已经存在)
        'ZCARD', // 获取已排序集合中的成员数
        'ZCOUNT', // 用给定值内的分数计算已排序集合中的成员
        'ZINCRBY', // 增加已排序集合中成员的得分
        'ZINTERSTORE', // 与多个已排序集相交，并将得到的已排序集存储在一个新键中
        'ZLEXCOUNT', // 计算给定词典范围内已排序集中的成员数
        'ZRANGE', // 按索引返回已排序集合中的成员范围
        'ZRANGEBYLEX', // 按字典序范围返回已排序集合中的成员范围
        'ZREVRANGEBYLEX', // 按字典编纂范围，从高到低的字符串排序，返回一个已排序集合中的成员范围
        'ZRANGEBYSCORE', // 按分数返回已排序集合中的成员范围
        'ZRANK', // 确定已排序集合中成员的索引
        'ZREM', // 从已排序的集合中删除一个或多个成员
        'ZREMRANGEBYLEX', // 删除给定词典范围内已排序集中的所有成员
        'ZREMRANGEBYRANK', // 删除给定索引中已排序集中的所有成员
        'ZREMRANGEBYSCORE', // 删除给定分数内已排序集合中的所有成员
        'ZREVRANGE', // 按索引返回已排序集合中成员的范围，得分从高到低排序
        'ZREVRANGEBYSCORE', // 按分数返回已排序集合中成员的范围，分数从高到低排序
        'ZREVRANK', // 确定一个成员在一个排序集的索引，分数从高到低排序
        'ZSCORE', // 获取排序集中与给定成员关联的分数
        'ZUNIONSTORE', // 添加多个排序集，并将得到的排序集存储在一个新键中
        'SCAN', // 递增地迭代键空间
        'SSCAN', // 增量迭代集合元素
        'HSCAN', // 递增迭代哈希字段和关联值
        'ZSCAN', // 增量迭代排序集元素和相关的分数
    ];

    //资源redis套接字连接
    private $_socket = false;


    //在序列化此组件时关闭连接。
    public function __sleep()
    {
        $this->close();
        return array_keys(get_object_vars($this));
    }

    /**
     * 返回一个值，该值指示是否建立了数据库连接。
     * @return DB连接是否建立
     */
    public function getIsActive()
    {
        return $this->_socket !== false;
    }

    /**
     * 建立数据库连接。
     */
    public function open()
    {
        if ($this->_socket !== false) {
            return;
        }
        $connection = ($this->unixSocket ?: $this->hostname . ':' . $this->port) . ', database=' . $this->database;

        $this->_socket = @stream_socket_client(
            $this->unixSocket ? 'unix://' . $this->unixSocket : 'tcp://' . $this->hostname . ':' . $this->port,
            $errorNumber,
            $errorDescription,
            $this->connectionTimeout ? $this->connectionTimeout : ini_get('default_socket_timeout'),
            $this->socketClientFlags
        );
        if ($this->_socket) {
            if ($this->dataTimeout !== null) {
                stream_set_timeout($this->_socket, $timeout = (int) $this->dataTimeout, (int) (($this->dataTimeout - $timeout) * 1000000));
            }
            if ($this->password !== null) {
                $this->executeCommand('AUTH', [$this->password]);
            }
            if ($this->database !== null) {
                $this->executeCommand('SELECT', [$this->database]);
            }
            $this->initConnection();
        } else {
            throw new Exception($message, $errorDescription, $errorNumber);
        }
    }

    //关闭当前活动的数据库连接。
    public function close()
    {
        if ($this->_socket !== false) {
            $connection = ($this->unixSocket ?: $this->hostname . ':' . $this->port) . ', database=' . $this->database;
            \Yii::trace('Closing DB connection: ' . $connection, __METHOD__);
            $this->executeCommand('QUIT');
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
            $this->_socket = false;
        }
    }

    /**
     * 在建立DB连接之后立即调用此方法。
     */
    protected function initConnection()
    {
        $this->trigger(self::EVENT_AFTER_OPEN);
    }

    /**
     * 返回当前[[dsn]]的DB驱动程序的名称
     */
    public function getDriverName()
    {
        return 'redis';
    }

    /**
     * @return LuaScriptBuilder
     */
    public function getLuaScriptBuilder()
    {
        return new LuaScriptBuilder();
    }

    /**
	 * 允许通过魔法方法发出所有支持的命令
     */
    public function __call($name, $params)
    {
        $redisCommand = strtoupper(self::camel2words($name, false));
        if (in_array($redisCommand, $this->redisCommands)) {
            return $this->executeCommand($redisCommand, $params);
        } else {
            return parent::__call($name, $params);
        }
    }

    //将CamelCase名称转换为空格分隔的单词
    public static function camel2words($name, $ucwords = true)
    {
        $label = trim(strtolower(str_replace([
            '-',
            '_',
            '.',
        ], ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name))));
        return $ucwords ? ucwords($label) : $label;
    }

    /**
	 * 参数数组应该包含由空格分隔的参数，例如执行
     */
    public function executeCommand($name, $params = [])
    {
        $this->open();

        $params = array_merge(explode(' ', $name), $params);
        $command = '*' . count($params) . "\r\n";
        foreach ($params as $arg) {
            $command .= '$' . mb_strlen($arg, '8bit') . "\r\n" . $arg . "\r\n";
        }

        fwrite($this->_socket, $command);

        return $this->parseResponse(implode(' ', $params));
    }

    /**
     * @param string $command
     * @return mixed
     * @throws 异常在错误
     */
    private function parseResponse($command)
    {
        if (($line = fgets($this->_socket)) === false) {
            throw new Exception("Failed to read from socket.\nRedis command was: " . $command);
        }
        $type = $line[0];
        $line = mb_substr($line, 1, -2, '8bit');
        switch ($type) {
            case '+': // 状态回复
                if ($line === 'OK' || $line === 'PONG') {
                    return true;
                } else {
                    return $line;
                }
            case '-': // 错误回复
                throw new Exception("Redis error: " . $line . "\nRedis command was: " . $command);
            case ':': // 整数回复
                return $line;
            case '$': // 批量回复
                if ($line == '-1') {
                    return null;
                }
                $length = (int)$line + 2;
                $data = '';
                while ($length > 0) {
                    if (($block = fread($this->_socket, $length)) === false) {
                        throw new Exception("Failed to read from socket.\nRedis command was: " . $command);
                    }
                    $data .= $block;
                    $length -= mb_strlen($block, '8bit');
                }

                return mb_substr($data, 0, -2, '8bit');
            case '*': // Multi-bulk回复
                $count = (int) $line;
                $data = [];
                for ($i = 0; $i < $count; $i++) {
                    $data[] = $this->parseResponse($command);
                }

                return $data;
            default:
                throw new Exception('Received illegal data from redis: ' . $line . "\nRedis command was: " . $command);
        }
    }
}

