<?php

/**
 * Класс-родитель для всех синглтонов. Наследовать тогда, когда экземпляр класса всего один. 
 * Например, роутер, реестр, пейдж-контроллер, контроллеры страниц:
 * sheldon/page.php:                class Page extends \Sheldon\Library\Singleton {}
 * sheldon/controller/any.php:      $page = Page::get();
 * 
 * @author Ilya Doroshin
 */
 
namespace Core\Abstracts;

abstract class Singleton extends Common
{
    # nothing happens here
    protected function __construct() {}
    
    /**
    * функция инициализации синглтона
    * возвращает объект вызываемого класса, инициализированный один раз
    * @return AbstractSingleton 
    */
    final public static function get($calledMethod = false)
    {
        static $aoInstance = array();

        $calledClassName = get_called_class();
      
        if (!isset($aoInstance[$calledClassName]))
        {
            $aoInstance[$calledClassName] = new $calledClassName($calledMethod);            
        }

        // echo $calledClassName;
        // echo '<br />';

        return $aoInstance[$calledClassName];
    }
    
    /**
    * объект синглтона нельзя клонировать!
    */
    final private function __clone() {}
}