<?php

namespace Managers\Controller;

class News extends \Core\Abstracts\Singleton
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

    public function edit($newsId)
    {
        $this->page['item'] = \Core\Model\News::find($newsId);
        $this->_form();
    }
    
    public function remove($newsId)
    {
        \Core\Model\News::find($newsId)->delete();
        $this->router->go($this->router->generate('manage_news_index'));        
    }
    
    public function index()
    {
        $this->page['items'] = \Core\Model\News::all(array('order' => 'name'));
        $this->page->display('news/index.twig');
    }
    
    private function _form()
    {
        //$this->page['categories'] = \Core\Model\Articlecategory::getAll();
        //$this->page['authors']    = \Core\Model\Author::getAll();
        $this->page->display('news/form.twig');
    }
    
    private function _proceed($values)
    {
        $item = empty($values['id'])
            ? new \Core\Model\News()
            : \Core\Model\News::find($values['id']);
        
        $item->name        = $values['name'];
        $item->announce    = $values['announce'];
        $item->content     = $values['content'];
        //$item->author_id   = $values['author'];
        //$item->category_id = $values['category'];
        $item->save();
        
        $picture = new \Core\Model\Newspicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $item, $picture);
        
        $this->router->go($this->router->generate('manage_news_index'));
    }
}