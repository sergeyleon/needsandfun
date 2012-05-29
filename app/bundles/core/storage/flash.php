<?php

namespace Core\Storage;

class Flash extends Session implements Common
{
    private $_flashes = array();
    private $_key     = '_flashes_';
    
    protected function __construct() {}
    
    public function getValue($name)
    {
        $flashes = (array) parent::getValue($this->_key, true);

        $result = false;
        
        if (isset($flashes[$name]))
        {
            $result = $flashes[$name];
        }
        
        return $result;
    }
    
    public function setValue($name, $value)
    {
        array_push($this->_flashes, $name);
        
        $array = (array) parent::getValue($this->_key, true);        
        $array[$name] = $value;
        
        parent::setValue($this->_key, $array);
    }
    
    public function purge()
    {
        $flashes = (array)parent::getValue($this->_key, true);
        
        foreach (array_keys($flashes) as $key)
        {
            if (!in_array($key, $this->_flashes))
            {
                unset($flashes[$key]);
            }
        }
        parent::setValue($this->_key, $flashes);
    }
}