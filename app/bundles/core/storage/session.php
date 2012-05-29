<?php

namespace Core\Storage;

class Session extends \Core\Abstracts\Singleton implements Common
{
    private $_session = false;
    
    public function getValue($name, $keep = true)
    {
        if (!$this->_session) $this->_startSession();
        
        $value = false;
        
        if (isset($_SESSION[$name]))
        {
            $value = json_decode($_SESSION[$name]);
            if (!$keep)
            {
                $this->remove($name);
            }
        }
        
        return $value;
    }
    
    public function setValue($name, $value)
    {
        if (!$this->_session) $this->_startSession();    
        $_SESSION[$name] = json_encode($value);
    }

    public function remove($name)
    {
        if (isset($_SESSION[$name]))
        {
            unset($_SESSION[$name]);
        }    
    }
    
    private function _startSession()
    {
        if (!session_id()) session_start();
        $this->_session = true;
    }    
}