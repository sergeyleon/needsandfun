<?php

/**
 * Базовый класс, от которого наследуются остальные
 *
 * @author Ilya Doroshin
 */
 
namespace Core\Abstracts;

class Common
{
    private $_router   = null;
    private $_registry = null;
    private $_page     = null;   
    private $_storage  = null;       
    private $_env      = null;

    /**
     * шорткаты к основным методам
     * вызываемые методы возвращают экземпляр одиночного объекта, 
     * поэтому, мы можем без проблем это тут использовать
     */
    public function __get($name)
    {
        if      ('page'           == $name) $result = $this->getPage();
        else if ('env'            == $name) $result = $this->getEnvironment();                
        else if ('router'         == $name) $result = $this->getRouter();
        else if ('registry'       == $name) $result = $this->getRegistry();
        else if ('flashStorage'   == $name) $result = $this->getStorage('flash');
        else if ('sessionStorage' == $name) $result = $this->getStorage('session');
        else if ('cookieStorage'  == $name) $result = $this->getStorage('storable');        
        else {
            $result = false;
            $callee = next(debug_backtrace());
            trigger_error("Undefined property {$callee['class']}::$$name", E_USER_NOTICE);
        }
        
        return $result;
    }
    
    /**
     * Обертка для роутера
     */
    public function getRouter() 
    {
        $router = $this->_router 
            ? $this->_router 
            : \Core\Router::get();
            
        return $router;
    }
    
    /**
     * Обертка для окружения
     */
    public function getEnvironment() 
    {
        $env = $this->_env 
            ? $this->_env 
            : \Core\Environment::get();
            
        return $env;
    }    
    
    /**
     * Обертка для реестра
     */    
    public function getRegistry() 
    {
        $registry = $this->_registry
            ? $this->_registry 
            : \Core\Registry::get();
            
        return $registry;
    }    
    
    /**
     * Обертка для шаблонизатора
     */    
    public function getPage() 
    {
        $page = $this->_page
            ? $this->_page 
            : \Core\Page::get();
            
        return $page;
    }
    
    public function getStorage($type = 'flash')
    {    
        $this->storage = \Core\Storage::init($type);
        return $this->storage;
    }    
}
 
 