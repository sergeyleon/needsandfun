<?php

namespace Needsandfun\Controller;

class Image extends \Core\Abstracts\Authorized
{
    public function show($filename, $type = 'full')
    {
        $picture = \Core\Model\Picture::resize($filename, $type);
        
        if ($picture)
        {
            $this->router->reload();
        }
    } 
}