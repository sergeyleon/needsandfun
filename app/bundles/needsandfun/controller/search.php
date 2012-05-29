<?php

namespace Needsandfun\Controller;

class Search extends \Core\Abstracts\Authorized
{
    public function index()
    {
    	if (isset($_GET['q'])) 
    	{
    		$this->page['searchResults'] = \Core\Model\Search::get()->find($_GET['q'], $_GET['filter']);
    	}

    	$this->page['shopBanners'] = \Core\Model\Banner::shop();
    	$this->page['actual'] 	   = \Core\Model\Event::thisWeek(4);
        $this->page->display('search/index.twig');
    }
}