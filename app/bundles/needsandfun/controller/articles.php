<?php

namespace Needsandfun\Controller;

class Articles extends \Core\Abstracts\Authorized
{
    public function article($article)
    {
        $this->page['item'] = \Core\Model\Article::from_url($article);
        $this->page['shopBanners'] = \Core\Model\Banner::shop(4);
        
        $this->page->display('articles/article.twig');
    }

    public function index()
    {
        $this->page['categories']  = \Core\Model\Articlecategory::getAll();
        $this->page['articles']    = \Core\Model\Article::getAll();
        $this->page['shopBanners'] = \Core\Model\Banner::shop();
        
        $this->page->display('articles.twig');
    }    
}