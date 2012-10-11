<?php

namespace Core\Model;

class Article extends \ActiveRecord\Model implements Itemswithpics, Searchable, Humanizeurl
{
    static $perPage = 24;
    
    static $has_many = array(
        array('articlepictures', 'order' => 'weight', 'foreign_key' => 'item_id'),
        array('articles')
    );

    static $belongs_to = array(
        array('category', 'class' => 'Articlecategory', 'foreign_key' => 'category_id'),
        array('author')
    );

    static $before_save = array('updateLink');

    public function updateLink()
    {
        $this->link = \Core\Url::encode($this->name);
    }

    public function get_url()
    {
        $url = \Core\Router::get()->generate('articles_article', array('article' => $this->encoded_key));
        return $url;
    }

    public function get_encoded_key()
    {
        return $this->link;
    }

    static function from_url($url)
    {
        $result = self::find_by_link($url);
        return $result;
    }

    public function getType()
    {
        return 'article';
    }

    public function getBindId()
    {
        return $this->id;
    }    
    /**
     * генерируем массив с иконками
     */
    public function getIcons()
    {
        return $this->articlepictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }
    
    public function getMaxPicWeight() 
    {
        $weight = Articlepicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }    


    
    static function banners()
    {
        $result = self::all(array(
            'conditions' => 'deleted is null',
            'limit'      => 5,
            'order'      => 'created desc'
        ));

        return $result;
    }
    

    static function getAll($_options = array())
    {
        $options = array(
            'conditions' => 'deleted is null',
            'order'      => 'name'
        );

        if (!empty($_options['limit']))
        {
            $options['limit'] = $_options['limit'];
        }

        if (!empty($_options['order']))
        {
            $options['order'] = $_options['order'];
        }

        return self::all($options);
    }
    
    static function getPager($page, $category = false, $route, $total = 0)
    {
        $options = array();
        $routeParams['current'] = $page;

        if ($category)
        {
            $routeParams['category'] = $category;
            $options['category'] = $category;
        }

        $pager = array(
            'total'   => ceil(count($total)/self::$perPage),
            'current' => $page,
            'route'   => $route,
            'routeParams' => $routeParams
        );

        if ($pager['total'] == 1)
        {
            $pager = false;
        }

        return $pager;
    }
    
}