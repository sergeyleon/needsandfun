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
/*
    public function index()
    {
        $this->page['categories']  = \Core\Model\Articlecategory::getAll();
        $this->page['articles']    = \Core\Model\Article::getAll();
        $this->page['shopBanners'] = \Core\Model\Banner::shop();
        
        $this->page->display('articles.twig');
    }    
*/    
    private function _categories($selected = false)
    {    
        $this->page['articleCategories'] = \Core\Model\Articlecategory::getAll($selected);
    }
    
    public function index($page = 1, $categories = false, $category = false)
    {
        $this->_categories();
      //  $this->page['articleCategories'] = \Core\Model\Articlecategory::getAll();
        
        $total = \Core\Model\Article::all();

        $options['limit']  = \Core\Model\Article::$perPage;
        $options['offset'] = \Core\Model\Article::$perPage * ($page - 1);
        
        $this->page['pager']   = \Core\Model\Article::getPager($page, $category, 'articles_index_page', $total);

        $this->page['items'] = \Core\Model\Article::all(array('conditions' => array('deleted is null'), 
          'limit' => $options['limit'],
          'offset' => $options['offset'],
          'order' => "created DESC"
         ));

        
        $this->page->display('articles/index.twig');
    }


    

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
    
    public function category($category, $page = 1) 
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
    
}