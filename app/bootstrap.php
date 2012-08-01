<?php

require_once 'constants.php';
require_once APP . '/bundles/core/engine.php';

/**
 * Отлавливаем ошибки типа Error
 * и показываем их
 */
try {
    \Core\Engine::init();    
} catch(\Core\Error $error) {
    $error->display();
}
