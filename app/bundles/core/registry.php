<?php
/**
 * Реестр значений, синглтон с ArrayAccess.
 * Пример использования: 
 * $registry = Registry::get();
 * $registry['variable'] = 'Hello, world!';

 * $registry['variable']; 
 * > Hello, world!
 * 
 * @author Ilya Doroshin
 */
 
namespace Core;
    
class Registry extends Abstracts\Singleton implements \ArrayAccess
{
    private $_variables;
    
    public function offsetExists($offset)
    {
        return isset($this->_variables[$offset])
            ? true
            : false;
    }
    
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset)
            ? $this->_variables[$offset]
            : false;
    }
    
    public function offsetSet($offset, $value)
    {
        $this->_variables[$offset] = $value;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset))
        {
            unset($this->_variables[$offset]);
        }
    }
}