<?php

namespace Managers\Controller;

class Pages extends \Core\Abstracts\Singleton
{

    public function __construct()
    {
        if (isset($_POST['proceed']))
        {
            $this->_proceed($_POST);
        }
        else {
            Index::drawMenu();                
        }
    }
    
    public function index()
    {
        $page = $this->getPage();
        $page['items'] = \Core\Model\Page::all(array('order' => 'page_type_id desc, name'));
        
        $page->display('pages/index.twig');
    }
    
    public function edit($pageId)
    {
        $page = \Core\Model\Page::find($pageId);

        $this->page['item'] = $page;

        if ($page->isAboutPage())
        {
            $page->contents = json_decode($page->contents);
            $this->_form('pages/about_form.twig');
        }
        else
        {
            $this->_form();
        }
    }

    public function add()
    {
        $this->_form();
    }

    public function _form($template = 'pages/form.twig')
    {
        $this->page->display($template);
    }

    public function remove($pageId)
    {
        $page = \Core\Model\Page::find($pageId);

        if ($page->deletable())
        {
            $page->delete();
        }
        else
        {
            $this->page->setMessage('Данную страницу удалить невозможно!');
        }

        $this->router->go($this->router->generate('manage_pages'));
    }
    
    private function _proceed($values) 
    {
        $page = empty($values['id'])
            ? new \Core\Model\Page()
            : \Core\Model\Page::find($values['id']);    
        
        $page->name     = $values['name'];
        $page->meta_keywords     = $values['meta_keywords'];
        $page->meta_description     = $values['meta_description'];

        if ($page->deletable())
        {
            $page->link         = $values['link'];    
            $page->page_type_id = \Core\Model\PageType::$INFOPAGE;
        }

        if ($page->isAboutPage())
        {
            $page->contents = json_encode($values['contents']);
        }
        else
        {
            $page->contents = $values['contents'];
            $page->meta_keywords     = $values['meta_keywords'];
            $page->meta_description     = $values['meta_description'];
        }

        $page->save();
        $this->getRouter()->go($this->getRouter()->generate('manage_pages'));
    }
    
}