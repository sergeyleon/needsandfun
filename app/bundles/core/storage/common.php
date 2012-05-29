<?php

namespace Core\Storage;

interface Common 
{
    public function getValue($name);
    public function setValue($name, $value);
    public function remove($name);    
}