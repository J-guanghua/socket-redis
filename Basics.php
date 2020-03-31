<?php

namespace guanghua\queue\socketRedis;


class Basics
{
    //返回该类的完全限定名
    public static function className()
    {
        return get_called_class();
    }

    //数组$config名值对，将用于初始化对象属性
    public function __construct($config = [])
    {
        if (!empty($config)) {
            static::configure($this, $config);
        }
        $this->init();
    }

    //初始化对象。
    public function init()
    {
    }

    //使用初始属性值配置对象。
    public static function configure($object, $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    /**
     * @param 字符串$name属性名
     * @return 混合了属性值
     * 如果属性没有定义，@抛出
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new \Exception('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new \Exception('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
      * @param 字符串$name属性名或事件名
      * @param 混合了$value属性值
      * 如果属性没有定义，@抛出
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new \Exception('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new \Exception('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }
}
