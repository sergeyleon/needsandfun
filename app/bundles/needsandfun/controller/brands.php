<?php

namespace Needsandfun\Controller;

class Brands extends \Core\Abstracts\Authorized
{
	public function index()
    {
        $this->page['brands'] = \Core\Model\Brand::all(array(
            'conditions' => 'deleted is null',
            'order'      => 'name'
        ));
        
        $this->page->display('brands.twig');
    }    
}