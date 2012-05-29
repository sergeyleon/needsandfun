<?php

namespace Needsandfun\Controller;

class Friends extends \Core\Abstracts\Authorized
{
	public function index()
    {
        $this->page['friends'] = \Core\Model\Partner::all(array(
            'conditions' => 'deleted is null',
            'order'      => 'name'
        ));

        $this->page['shopBanners']   = \Core\Model\Banner::shop();
        
        $this->page->display('friends.twig');
    }    
}