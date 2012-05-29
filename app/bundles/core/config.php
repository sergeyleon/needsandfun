<?php

/**
 * Класс конфига
 * Для работы необходима библиотека ZendConfig
 *
 * @author Илья Дорошин
 */
namespace Core;

class Config
{
    private function __construct() {}
    private function __clone() {}    
    
    static public function load($file = false)
    {
        /**
         * т.к. все конфиги у нас в yaml
         */
        $file = CONFIGS . '/' . $file;
     
        if (!$file)
        {
            throw new Error('Вы не указали конфиг!');
        }
        else if (!is_file($file))
        {
            throw new Error('Файл: ' . $file . ' не существует!');        
        }
        
        $path = pathinfo($file);
        
        switch ($path['extension'])
        {
            case 'yml':
                require_once LIBRARY . '/Zend/Config/Yaml.php';                        
                $config = new \Zend_Config_Yaml($file);
                break;
                
            case 'ini':
                require_once LIBRARY . '/Zend/Config/Ini.php';                        
                $config = new \Zend_Config_Ini($file);
                break;
                
            default:
                throw new Error('Невозможно открыть конфиг такого типа!');
                break;
        }
        
        $config = json_encode($config->toArray());
        $config = json_decode($config);
        return $config;
    }
}
 