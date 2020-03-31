<?php

namespace guanghua\redis;


class Monitor extends Basics
{
    
    //将附加的事件处理程序(事件名称=>处理程序)数组化
    private $_events = [];

    //此方法在通过克隆现有对象创建对象后调用。
    public function __clone()
    {
        $this->_events = [];
    }


    //当事件被触发时，要传递给事件处理程序的数据
    public function on($name, $handler, $data = null, $append = true)
    {   
        if ($append || empty($this->_events[$name])) {
            $this->_events[$name][] = [$handler, $data];
        } else {
            array_unshift($this->_events[$name], [$handler, $data]);
        }
    }

    //如果为空，则将删除附加到指定事件的所有处理程序。
    public function off($name, $handler = null)
    {  
        if (empty($this->_events[$name])) {
            return false;
        }
        if ($handler === null) {
            unset($this->_events[$name]);
            return true;
        }

        $removed = false;
        foreach ($this->_events[$name] as $i => $event) {
            if ($event[0] === $handler) {
                unset($this->_events[$name][$i]);
                $removed = true;
            }
        }
        if ($removed) {
            $this->_events[$name] = array_values($this->_events[$name]);
        }
        return $removed;
    }

    //将创建一个默认的[[Event]]对象。
    public function trigger($name, $event = null)
    { 
        if (!empty($this->_events[$name])) {
            foreach ($this->_events[$name] as $handler) {
                call_user_func($handler[0], $event);
            }
        }
    }

}
