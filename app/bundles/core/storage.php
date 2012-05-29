<?php

namespace Core;

class Storage extends Abstracts\Singleton
{       

    protected function __construct() {}

    static function init($type)
    {
        self::get();
        
        if ('flash' == $type)
        {
            $storage = Storage\Flash::get();
        }
        else if ('storable' == $type)
        {
            $storage = Storage\Cookie::get();            
        }
        else if ('session' == $type)
        {
            $storage = Storage\Session::get();            
        }
        
        return $storage;
    }
    
    public function __destruct()
    {    
        /*
         * чистим flash-messages
         */
        Storage\Flash::get()->purge();
    }
}