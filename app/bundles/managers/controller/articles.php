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
        else if (isset($_POST['proceed_category']))
        {
            $this->_proceedCategory($_POST);
        }
        else 
        {
            Index::drawMenu();                
        }
    }
    
    
    private function _proceedCategory($values)
    {
        $category = isset($values['id'])
            ? \Core\Model\Articlecategory::find($values['id'])
            : new \Core\Model\Articlecategory();
            
        $category->name       = $values['name'];
       // $category->is_visible = $values['visible'];

      //  $category->description      = $values['description'];
        $category->meta_keywords    = $values['meta_keywords'];
        $category->meta_description = $values['meta_description'];

       // $category->parent_id  = 'root' != $values['parent'] 
      //      ? $values['parent']
      //      : null;

        $category->save();
        
        $this->router->go($this->router->generate('manage_articles_index'));
    }
    
    public function addCategory()
    {
        $category = new \Core\Model\Articlecategory();
        $category->name = $_POST['categoryName'];
        $category->save();
        
        $this->router->go($this->router->generate('manage_articles_index'));
    }
    
    public function removeCategory()
    {
        $categoryId = $_POST['deleteCategory'];
        $category =\Core\Model\Articlecategory::find($categoryId);
        $category->deleted = new \DateTime();
        $category->save();
    }
    
    public function editCategory($categoryId)
    {
        $this->page['item'] = \Core\Model\Articlecategory::find($categoryId);
        $this->page['categories'] = \Core\Model\Articlecategory::all(array('conditions' => array('deleted is null and id = ?', $categoryId), 'order' => 'name'));
        $this->page->display('articles/category.twig');
    }
    
    public function categoryVisibility()
    {
        $category = \Core\Model\Articlecategory::find($_POST['categoryId']);
        $func = $_POST['visible'] 
            ? 'show'
            : 'hide';

        $category->$func();
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


///////////////////////////////////
    private function _getArticles($page = 1, $categories = false, $category = false)
    {

        $conditions = array(
            'articles.deleted is null'
        );

        if ($categories)
        {
            $conditions[] = ' articles.category_id = '. $categories .' ';
        }

        $options = array('conditions' => array(implode(' AND ', $conditions)));
        
        if (!empty($variables))
        {
            $options['conditions'] = array_merge($options['conditions'], $variables);
        }

 
        if (is_numeric($page))
        {
            $total = \Core\Model\Article::all($options);

            $options['limit']  = \Core\Model\Article::$perPage;
            $options['offset'] = \Core\Model\Article::$perPage * ($page - 1);

            $this->page['pager']   = \Core\Model\Article::getPager($page, $category, 'manage_articles_category_page', $total);
            
          //  var_dump($this->page['pager']);
        }
        
        $options['order']  = 'name';

        $goods = \Core\Model\Article::all($options);

        if (isset($total) && count($total) > 0 && count($goods) == 0 && $page > 1)
        {
            $this->router->go($this->router->generate('category_page', array('page' => 1)));
        }

        return $goods;
    }

    private function _categories($selected = false)
    {    
        $this->page['articleCategories'] = \Core\Model\Articlecategory::getAll($selected);
    }

    public function category_index($category, $page = 1) 
    {
        if($category != 'new') {
          $this->page['currentCategory'] = \Core\Model\Articlecategory::from_url($category);
          $category = $this->page['currentCategory']->encoded_key;
  
          $categoryId = $this->page['currentCategory']->id;
  
          $filters = array('categoryId' => $categoryId);
          $this->getStorage('flash')->setValue('categoryId', $categoryId);
          $this->_categories($categoryId);
              
          $categories = $categoryId;
        }
        
        $this->page['items'] = $this->_getArticles($page, $categories, $category);

        $this->page['categories'] = \Core\Model\Articlecategory::getAll();

        $this->page->display('articles/index.twig');

    }
    
    
    public function index($page = 1, $categories = false, $category = false, $query = false)
    {
        $this->page['categories'] = \Core\Model\Articlecategory::getAll();

        $total = \Core\Model\Article::all();

        $options['limit']  = \Core\Model\Article::$perPage;
        $options['offset'] = \Core\Model\Article::$perPage * ($page - 1);
        
        $this->page['pager']   = \Core\Model\Article::getPager($page, $category, 'manage_articles_index_page', $total);

        $this->page['items'] = \Core\Model\Article::all(array('conditions' => array('deleted is null'), 
          'limit' => $options['limit'],
          'offset' => $options['offset'],
          'order' => "created DESC"
         ));
          
        $this->page->display('articles/index.twig');
    }    
    
///////////////////////////////////
 /*
    public function index()
    {
       // $this->page['selectedCategory'] = $this->cookieStorage->getValue('articles_selectedCategory') ?: 'all';
        $this->page['categories'] = \Core\Model\Articlecategory::getAll();
        
        $this->page['items'] = \Core\Model\Article::all(array('order' => 'name'));
        $this->page->display('articles/index.twig');
    }
 */   
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