<?php

namespace Needsandfun\Controller;

class Pages extends \Core\Abstracts\Authorized
{   
    public function show($link)
    {
        $page = \Core\Model\Page::find(array('conditions' => array('link = ?', $link)));

        if (!$page)
        {
            $this->router->errorPage();
            die();
        }

        $this->page['item'] = $page;

        $this->page['actual'] = \Core\Model\Event::thisWeek(4);
        $this->page['shopBanners']  = \Core\Model\Banner::shop(4);
        
        $this->page->display('shop/page.twig');
    }
}