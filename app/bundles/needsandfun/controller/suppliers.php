<?php

namespace Needsandfun\Controller;

class Suppliers extends \Core\Abstracts\Authorized
{
	public function index()
    {
        $this->page['suppliers'] = \Core\Model\Supplier::all(array(
            'conditions' => 'deleted is null',
            'order'      => 'name'
        ));
        
        $this->page->display('suppliers.twig');
    }    
}