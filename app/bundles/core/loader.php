<?php

/**
 * Автозагрузчик классов
 * @author Ilya Doroshin
 */
namespace Core;

class Loader 
{
    static function loadClass($className)
    {
      $path = strtolower(preg_replace('/([a-z])([A-Z])/', '$1/$2', $className));
      $path = str_replace('\\', '/', $path);
      $path = BUNDLES . '/' . $path . '.php';

      if(file_exists($path))
         require_once $path;
    } 
    
    static function register()
    {    
        spl_autoload_register(__NAMESPACE__ . "\Loader::loadClass");
    }
    
    static function unregister()
    {
        spl_autoload_unregister(__NAMESPACE__ . "\Loader::loadClass");
    }  
}


