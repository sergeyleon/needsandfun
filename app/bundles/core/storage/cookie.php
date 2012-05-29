<?php

namespace Core\Storage;

class Cookie extends \Core\Abstracts\Singleton implements Common
{

    private $_defaults = array(
        'expire' => 86400,     # сутки
        'path'   => '/'        # путь от корня
    );
    
    public function getValue($name)
    {
        $value = false;
        if (isset($_COOKIE[$name]))
        {
            $value = json_decode($_COOKIE[$name]) ?: $_COOKIE[$name];
        }
        
        return $value;
    }
    
    public function setValue($name, $value)
    {
        $this->_cookie($name, json_encode($value));
    }

    public function remove($name)
    {
        $this->_cookie($name, false);
    }
    
    /**
     * работа с кукисами
     * является геттером при наличии одного параметра
     * сеттером — при наличии 2х или 3х параметров
     * 
     * @param string $name
     * @param string $value
     * @param number $expire
     * @return string
     */
    public function _cookie($name, $value = null, $expire = false)
    {
        $expire = $expire ?: (time() + $this->_defaults['expire']);
        $path   = $this->_defaults['path'];
        
        if (false === $value) $expire = 0;
        setcookie($name, $value, $expire, $path);    
    }
    
}