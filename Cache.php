<?php

namespace guanghua\redis;


abstract class Cache extends Monitor{
   
	//为了确保互操作性，应该只使用字母数字字符
    public $keyPrefix = 'cache';

    //用于序列化和反序列化缓存数据的函数。默认为null，表示
    public $serializer;
	
	//如果没有显式地指定持续时间，[[set()]]将使用此值。
    public $defaultDuration = 0;


    //获取字符长度
    public static function byteLength($string)
    {
        return mb_strlen($string, '8bit');
    }


	//如果给定的键是只包含字母数字字符且不超过32个字符的字符串，
    public function buildKey($key)
    {
        if (is_string($key)) {
            $key = ctype_alnum($key) && self::byteLength($key) <= 32 ? $key : md5($key);
        } else {
            $key = md5(json_encode($key));
        }

        return $this->keyPrefix . $key;
    }

	//使用指定的键从缓存中检索值。
    public function get($key)
    {
        $key = $this->buildKey($key);
        $value = $this->getValue($key);

        if ($value === false || $this->serializer === false) {
            return $value;
        } elseif ($this->serializer === null) {
            $value = unserialize($value);
        } else {
            $value = call_user_func($this->serializer[1], $value);
        }
        if (is_array($value) && !($value[1] instanceof Dependency && $value[1]->isChanged($this))) {
            return $value[0];
        } else {
            return false;
        }
    }
	
	//检查指定的键是否存在于缓存中。
	public function exists($key)
    {
        $key = $this->buildKey($key);
        $value = $this->getValue($key);

        return $value !== false;
    }


    //将键标识的值存储到缓存中。
    public function set($key, $value, $duration = null, $dependency = null)
    {
        if ($duration === null) {
            $duration = $this->defaultDuration;
        }

        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }
        if ($this->serializer === null) {
            $value = serialize([$value, $dependency]);
        } elseif ($this->serializer !== false) {
            $value = call_user_func($this->serializer[0], [$value, $dependency]);
        }
        $key = $this->buildKey($key);

        return $this->setValue($key, $value, $duration);
    }
	
	//从缓存中删除具有指定键的值
    public function delete($key)
    {
        $key = $this->buildKey($key);

        return $this->deleteValue($key);
    }

	//从缓存中删除所有值
    public function flush()
    {
        return $this->flushValues();
    }

	//使用指定的键从缓存中检索值。
    abstract protected function getValue($key);

	//在缓存中存储由键标识的值。
    abstract protected function setValue($key, $value, $duration);

	//如果缓存不包含此键，则将由键标识的值存储到缓存中。
    abstract protected function addValue($key, $value, $duration);

	//从缓存中删除具有指定键的值
    abstract protected function deleteValue($key);
	
	//从缓存中删除所有值。
    abstract protected function flushValues();

	//使用指定的键从缓存中检索多个值。
    protected function getValues($keys)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->getValue($key);
        }

        return $results;
    }

	//在缓存中存储多个键值对。
    protected function setValues($data, $duration)
    {
        $failedKeys = [];
        foreach ($data as $key => $value) {
            if ($this->setValue($key, $value, $duration) === false) {
                $failedKeys[] = $key;
            }
        }

        return $failedKeys;
    }

}

?>