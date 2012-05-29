<?php

/**
 * Класс ядра административного интерфейса. Синглтон.
 * Запускает все необходимые модули: registry, page, router, activerecord
 *
 * @author Илья Дорошин
 * @TODO Проверку на авторизацию можно поместить непосредственно в ядре,
 *       т.к. все страницы данного интерфейса требуют авторизацию в системе
 */
 
namespace Core;
 
require_once 'loader.php';
Loader::register();

class Engine extends Abstracts\Singleton
{
    /**
     * Конструктор класса движка
     */
    public function init()
    {   
        /**
         * странная хрень, которую нужно пофиксить!
         */
        session_start();
        
        $engine = self::get();
        
        /**
         * инициализируем текущее окружение
         */
        $env = $engine->getEnvironment()
                      ->setByHost($_SERVER['HTTP_HOST']);
        
        define('PHP_ACTIVERECORD_AUTOLOAD_DISABLE', true);
                      
        require_once LIBRARY . '/Activerecord/ActiveRecord.php';
        require_once LIBRARY . '/Upload/class.upload.php';        

        \ActiveRecord\Config::initialize(function($cfg) use ($env)
        {
            $engine = $env->variables->DB_ENGINE;
            $cfg->set_model_directory(MODELS);
            $cfg->set_connections(array(
            	'development' => 'mysql://' . $env->variables->$engine->DB_USER . ':'. $env->variables->$engine->DB_PASSWORD . '@' . $env->variables->$engine->DB_HOST . '/' . $env->variables->$engine->DB_NAME . '?charset=utf8'
            ));
        });                              
                      
        /**
         * инициализируем маршрутизатор
         */        
        $router = $engine->getRouter()
                         ->execute();        
    }
}