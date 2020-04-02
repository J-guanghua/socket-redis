<?php

namespace guanghua\redis;

use Exception;

//改下session 存储方式
class RedisSession extends Monitor
{
    public $id = "session_";
    
    //缓存组件
    public $redis = 'redis';
    
    //前缀在每个缓存键上的字符串
    public $keyPrefix;

    //DEBUG 调试
    public $debug = true;
    
    public $flashParam = '__flash';
    
    private $_cookieParams = ['httponly' => true];
    
    //初始化redis会话组件。
    public function init()
    {

        $this->redis = guanghua::ensure($this->redis,RedisConn::class);

        if ($this->keyPrefix === null) {
            $this->keyPrefix = substr(md5($this->id), 0, 5);
        }
        register_shutdown_function([$this, 'close']);
        if ($this->getIsActive()) {
            $this->updateFlashCounters();
        }
    }

    //启动Session 会话。
    public function open()
    {
        if ($this->getIsActive()) {
            return;
        }

        $this->registerSessionHandler();

        $this->setCookieParamsInternal();

        @session_start();

        if ($this->getIsActive()) {
            $this->updateFlashCounters();
        } else {
            $error = error_get_last();
            throw new Exception($message  . get_class($this) . '::' . __METHOD__);
        }
    }
    //会话是否已经开始
    public function getIsActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
    
    //寄存器会话处理程序。
    protected function registerSessionHandler()
    {
        if ($this->debug) {
            session_set_save_handler(
                [$this, 'openSession'],
                [$this, 'closeSession'],
                [$this, 'readSession'],
                [$this, 'writeSession'],
                [$this, 'destroySession'],
                [$this, 'gcSession']
            );
        } else {
            @session_set_save_handler(
                [$this, 'openSession'],
                [$this, 'closeSession'],
                [$this, 'readSession'],
                [$this, 'writeSession'],
                [$this, 'destroySession'],
                [$this, 'gcSession']
            );
        }
    }
    //Session 话处理程序。 读取
    public function readSession($id)
    {   
        $data = $this->redis->executeCommand('GET', [$this->calculateKey($id)]);
        return $data === false || $data === null ? '' : $data;
    }

    //Session编写处理程序 写入
    //getTimeout 秒后 没有访问将自动销毁
    public function writeSession($id, $data)
    {   
        return (bool) $this->redis->executeCommand('SET', [$this->calculateKey($id), $data, 'EX', $this->getTimeout()]);
    }

    //Session会话销毁处理程序
    public function destroySession($id)
    {   
        $this->redis->executeCommand('DEL', [$this->calculateKey($id)]);
        return true;
    }

    //生成用于在缓存中存储会话数据的唯一键。
    protected function calculateKey($id)
    {
        return $this->keyPrefix . md5(json_encode([__CLASS__, $id]));
    }
    //设置会话cookie参数。
    private function setCookieParamsInternal()
    {
        $data = $this->getCookieParams();
        if (isset($data['lifetime'], $data['path'], $data['domain'], $data['secure'], $data['httponly'])) {
            session_set_cookie_params($data['lifetime'], $data['path'], $data['domain'], $data['secure'], $data['httponly']);
        } else {
            throw new Exception('Please make sure cookieParams contains these elements: lifetime, path, domain, secure and httponly.');

        }
    }

    //设置会话cookie参数的数组。
    public function getCookieParams()
    {
        return array_merge(session_get_cookie_params(), array_change_key_case($this->_cookieParams));
    }

    //默认缓存“session”是1440秒(或“session”的值)。设置在php.ini中的gc_maxlifetime)。
    public function getTimeout()
    {
        return (int) ini_get('session.gc_maxlifetime');
    }

    //将数据视为 “垃圾” 并清除的秒数
    public function setTimeout($value)
    {
        ini_set('session.gc_maxlifetime', $value);
    }

    //开启会话处理程序。
    public function openSession()
    {
        return true;
    }

    //会话处理程序。
    public function closeSession()
    {
        return true;
    }
    

    //会话GC(垃圾收集)处理程序。
    public function gcSession($maxLifetime)
    {
        return true;
    }

    //释放所有会话变量并销毁注册到会话的所有数据。
    public function destroy()
    {
        if ($this->getIsActive()) {
            $sessionId = session_id();
            $this->close();
            $this->setId($sessionId);
            $this->open();
            session_unset();
            session_destroy();
            $this->setId($sessionId);
        }
    }
    //返回具有会话变量名称的会话变量值
    public function get($key, $defaultValue = null)
    {
        $this->open();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
    }

    //添加会话变量。
    public function set($key, $value)
    {
        $this->open();
        $_SESSION[$key] = $value;
    }

    //删除会话变量。
    public function remove($key)
    {
        $this->open();
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);

            return $value;
        } else {
            return null;
        }
    }

    //删除所有会话变量
    public function removeAll()
    {
        $this->open();
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
    }

    //混合$key会话变量名
    public function has($key)
    {
        $this->open();
        return isset($_SESSION[$key]);
    }

    //更新flash消息的计数器并删除过时的flash消息。
    protected function updateFlashCounters()
    {   
        $counters = $this->get($this->flashParam, []);
        if (is_array($counters)) {
            foreach ($counters as $key => $count) {
                if ($count > 0) {
                    unset($counters[$key], $_SESSION[$key]);
                } elseif ($count == 0) {
                    $counters[$key]++;
                }
            }
            $_SESSION[$this->flashParam] = $counters;
        } else {
            // 修正了flashParam不返回数组的意外问题
            unset($_SESSION[$this->flashParam]);
        }
    }

    //获取会话ID
    public function getId()
    {
        return session_id();
    }

    //设置会话ID。
    public function setId($value)
    {
        session_id($value);
    }

    //结束当前会话并存储会话数据。
    public function close()
    {
        if ($this->getIsActive()) {
            $this->debug ? session_write_close() : @session_write_close();
        }
    }
}
