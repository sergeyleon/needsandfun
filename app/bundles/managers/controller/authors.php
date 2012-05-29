<?php

namespace Managers\Controller;

class Authors extends \Core\Abstracts\Singleton
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

    public function edit($authorId)
    {
        $this->page['item'] = \Core\Model\Author::find($authorId);
        $this->_form();
    }
    
    public function remove($authorId)
    {
        \Core\Model\Author::find($authorId)->delete();
        $this->router->go($this->router->generate('manage_articles_authors_index'));        
    }
    
    public function index()
    {
        $this->page['items'] = \Core\Model\Author::all(array('order' => 'name'));
        $this->page->display('authors/index.twig');
    }
    
    private function _form()
    {
        $this->page->display('authors/form.twig');
    }
    
    private function _proceed($values)
    {
        $item = empty($values['id'])
            ? new \Core\Model\Author()
            : \Core\Model\Author::find($values['id']);
        
        $item->name        = $values['name'];
        $item->description = $values['description'];
        $item->save();
        
        $picture = new \Core\Model\Authorpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $item, $picture);
        
        $this->router->go($this->router->generate('manage_articles_authors_index'));
    }
}