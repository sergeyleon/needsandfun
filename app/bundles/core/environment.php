<?php

namespace Core;

class Environment extends \Core\Abstracts\Singleton
{    
    public $variables;
    /**
     * переменная массива окружений
     */
    private $_envs = array();
    /**
     * переменная массива хостов-ссылок 
     */    
    private $_hosts = array();         
    /**
     * переменная массива неймспейсов-ссылок 
     */    
    private $_namespaces = array();     
    
    
    
    /**
     * настройки по-умолчанию
     */
    private $_default = array(
        'name'      => 'default',
        'host'      => false,
        'namespace' => 'Core',
        'rewrite'   => false,
        'paths'     => array(
            'templates' => '_errors',
            'common'    => 'error'
        )
    );
    
    /**
     * имя окружения
     */    
    private $_name;
    
    /**
     * неймспейс окружения
     */        
    private $_namespace;
    
    /**
     * пути окружения
     */            
    private $_paths;
    
    /**
     * флаг, который обозначает, осуществлен ли редирект с common-папки в корень
     * - если флаг стоит:       /js/jquery.js
     * - если флаг не стоит:    /common/{$env->name}/js/jquery.js     
     */
    private $_pathsRewrited;
    
    
    /**
     * конструктор объекта
     * - обработка конфига
     * - заполнение переменных
     */
    protected function __construct()
    {
        $this->_paths = (object)$this->_paths;
        
        $this->_envs['default'] = json_decode(json_encode($this->_default));            
        
        $config = \Core\Config::load('environment.yml');        
        
        foreach ($config as $name => &$env)
        {
            if ('default' == $name)
            {
                throw new \Core\Error('Переопределение окружения по-умолчанию запрещено!');
            }
            
            $env->name = $name;
            $this->_envs[$name] = $env;
            
            $hosts = function($env) {
                $result = array();
                
                if (isset($env->hosts))       
                {
                    $hosts = explode(',', $env->hosts);
                    
                    foreach ($hosts as $host)
                    {
                        $result[trim($host)] = $env;                
                    }
                }
                else 
                {
                    throw new \Core\Error('Параметр host не указан!');    
                }        
                return $result;
            };
            
            foreach ($hosts($env) as $__host => $env)
            {
                $this->_hosts[$__host][$env->name] = $env;
            }
            
            if (isset($env->namespace))
            {
                if (!isset($this->_namespaces[$env->namespace]))
                {
                    $this->_namespaces[$env->namespace] = array();
                }
                $this->_namespaces[$env->namespace] = array_merge($this->_namespaces[$env->namespace], $hosts($env));
            }            
        }
        
        $this->set('default');
    }
    
    /**
     * метод определения/переопределения текущего окружения по имени хоста
     * @var string $host имя хоста
     */
    public function setByHost($host)
    {
        if (isset($this->_hosts[$host]))
        {
            $env = current($this->_hosts[$host]);
        }
        else 
        {
            throw new \Core\Error('Не найден домен!');            
        }
        
        $this->_setEnv($env);
        
        return $this;        
    }
    
    public function setByNamespace($namespace, $host = false)
    {
        if (!$host)
        {
            $host = $_SERVER['HTTP_HOST'];
        }

        if (isset($this->_namespaces[$namespace][$host]))
        {
            $env = $this->_namespaces[$namespace][$host];
        }
        else 
        {
            throw new \Core\Error('Не найден неймспейс!');            
        }
        
        $this->_setEnv($env);
        
        return $this;        
    }
    
    /**
     * метод определения/переопределения текущего окружения по имени
     * @var string $host имя окружения
     */    
    public function set($name)
    {

        if (isset($this->_envs[$name]))
        {
            $env = $this->_envs[$name];
        }
        else 
        {
            throw new \Core\Error('Не найдено окружение!');
        }
        
        $this->_setEnv($env);
        
        return $this;
    }
    
    /**
     * метод проставляет необходимые данные
     */
    private function _setEnv($env)
    {
        if (isset($env->name))
        {
            $this->_name          = $env->name;            
        }

        if (isset($env->namespace))
        {
            $this->_namespace     = $env->namespace;
        }
        
        if (isset($env->paths))
        {
            $this->_paths         = $env->paths;
        }
        
        if (isset($env->rewrite))
        {
            $this->_pathsRewrited = $env->rewrite;
        }
        
        if (isset($env->variables))
        {
            $this->variables = $env->variables;
        }        
    }
    
    /**
     * геттеры приватных свойств
     */
    public function getName()
    {
        return $this->_name;
    }
   
    public function getNamespace()
    {
        return $this->_namespace;
    }    
    
    public function isRewrited()
    {
        return !$this->_pathsRewrited;
    }    
    
    public function getPaths()
    {
        return $this->_paths;
    }
}

