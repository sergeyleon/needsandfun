<?php

namespace Needsandfun\Controller;

class News extends \Core\Abstracts\Authorized
{
    public function news($news)
    {
        $this->page['item'] = \Core\Model\News::from_url($news);
        $this->page['shopBanners'] = \Core\Model\Banner::news();
        
        $this->page->display('news/news.twig');
    }

    public function index()
    {
        //$this->page['categories']  = \Core\Model\Articlecategory::getAll();
        $this->page['news']    = \Core\Model\News::getAll();
        $this->page['shopBanners'] = \Core\Model\Banner::shop();
        
        $this->page->display('news.twig');
    }    
}