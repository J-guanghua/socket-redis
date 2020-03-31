<?php

namespace guanghua\redis;


class RedisCache extends Cache
{
    //redis连接对象
    public $redis;

    public function init()
    {
        parent::init();
        $this->redis = guanghua::redis();
    }

    //检查指定的键是否存在于缓存中。
    public function exists($key)
    {
        return (bool) $this->redis->executeCommand('EXISTS', [$this->buildKey($key)]);
    }

    //inheritdoc
    protected function getValue($key)
    {
        return $this->redis->executeCommand('GET', [$key]);
    }

    //inheritdoc
    protected function getValues($keys)
    {
        $response = $this->redis->executeCommand('MGET', $keys);
        $result = [];
        $i = 0;
        foreach ($keys as $key) {
            $result[$key] = $response[$i++];
        }

        return $result;
    }

    //inheritdoc
    protected function setValue($key, $value, $expire)
    {
        if ($expire == 0) {
            return (bool) $this->redis->executeCommand('SET', [$key, $value]);
        } else {
            $expire = (int) ($expire * 1000);

            return (bool) $this->redis->executeCommand('SET', [$key, $value, 'PX', $expire]);
        }
    }
    //inheritdoc
    protected function setValues($data, $expire)
    {
        $args = [];
        foreach ($data as $key => $value) {
            $args[] = $key;
            $args[] = $value;
        }

        $failedKeys = [];
        if ($expire == 0) {
            $this->redis->executeCommand('MSET', $args);
        } else {
            $expire = (int) ($expire * 1000);
            $this->redis->executeCommand('MULTI');
            $this->redis->executeCommand('MSET', $args);
            $index = [];
            foreach ($data as $key => $value) {
                $this->redis->executeCommand('PEXPIRE', [$key, $expire]);
                $index[] = $key;
            }
            $result = $this->redis->executeCommand('EXEC');
            array_shift($result);
            foreach ($result as $i => $r) {
                if ($r != 1) {
                    $failedKeys[] = $index[$i];
                }
            }
        }

        return $failedKeys;
    }

    /**
     * @inheritdoc
     */
    protected function addValue($key, $value, $expire)
    {
        if ($expire == 0) {
            return (bool) $this->redis->executeCommand('SET', [$key, $value, 'NX']);
        } else {
            $expire = (int) ($expire * 1000);

            return (bool) $this->redis->executeCommand('SET', [$key, $value, 'PX', $expire, 'NX']);
        }
    }

    /**
     * @inheritdoc
     */
    protected function deleteValue($key)
    {
        return (bool) $this->redis->executeCommand('DEL', [$key]);
    }

    /**
     * @inheritdoc
     */
    protected function flushValues()
    {
        return $this->redis->executeCommand('FLUSHDB');
    }
}
