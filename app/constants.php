<?php

/**
 * Файл с конфигурационными константами
 *
 * @author Илья Дорошин
 */

/**
 * пути в проекте
 */
define('ROOT',    realpath(dirname(__FILE__) 
    . DIRECTORY_SEPARATOR . '..' 
    . DIRECTORY_SEPARATOR . '..' 
    . DIRECTORY_SEPARATOR . '..'));                
                    
define('APP',     __DIR__);                        # приложения
define('BUNDLES', APP . '/bundles');               # пакеты
define('CONFIGS', APP . '/configs');               # конфиги
define('LIBRARY', APP . '/library');               # 3rd party libraries
define('MODELS',  BUNDLES . '/Core/model');        # модели


define('SITE',        $_SERVER['DOCUMENT_ROOT']);      # корень сайта
define('WWW',         '');                             # www-корень
define('COMMON',      WWW . '/common');                # фронтенд
define('WWW_UPLOADS', WWW . '/uploads/pics');      # папка загрузок

define('UPLOADS',     SITE . '/uploads/pics');         # папка картинок
define('ORIGINALS',   UPLOADS . '/_originals');         # папка картинок

/**
 * Указываем пути, где искать файлы для include/require
 */
set_include_path(get_include_path()
    . PATH_SEPARATOR . BUNDLES
    . PATH_SEPARATOR . CONFIGS
    . PATH_SEPARATOR . LIBRARY    
);

/**
 * настройки для шаблонизатора
 */
define('TEMPLATES',       APP . '/templates/');
define('TEMPLATES_CACHE', APP . '/templates/cache/');

/**
 * авторизационные ключи
 */
define('AUTH_TOKEN', md5('auth_token'));
define('AUTH_HASH',  md5('authorize!'));

/**
 * Настройки mysql
 */
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASSWORD', '123qwe');
define('DB_NAME', 'needsandfun');
