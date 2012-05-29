<?php

namespace Managers\Controller;

class Articles extends \Core\Abstracts\Singleton
{
    public function __construct()
    {
        if (isset($_POST['proceed']))
        {
            $this->_proceed($_POST);
        }
        else 
        {
            Index::drawMenu();                
        }
    }
    
    public function add()
    {
        $this->_form();
    }

    public function edit($articleId)
    {
        $this->page['item'] = \Core\Model\Article::find($articleId);
        $this->_form();
    }
    
    public function remove($articleId)
    {
        \Core\Model\Article::find($articleId)->delete();
        $this->router->go($this->router->generate('manage_articles_index'));        
    }
    
    public function index()
    {
        $this->page['items'] = \Core\Model\Article::all(array('order' => 'name'));
        $this->page->display('articles/index.twig');
    }
    
    private function _form()
    {
        $this->page['categories'] = \Core\Model\Articlecategory::getAll();
        $this->page['authors']    = \Core\Model\Author::getAll();
        $this->page->display('articles/form.twig');
    }
    
    private function _proceed($values)
    {
        $item = empty($values['id'])
            ? new \Core\Model\Article()
            : \Core\Model\Article::find($values['id']);
        
        $item->name        = $values['name'];
        $item->announce    = $values['announce'];
        $item->content     = $values['content'];
        $item->author_id   = $values['author'];
        $item->category_id = $values['category'];
        $item->save();
        
        $picture = new \Core\Model\Articlepicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $item, $picture);
        
        $this->router->go($this->router->generate('manage_articles_index'));
    }
}