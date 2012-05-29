<?php
/**
 * Модель пользователя. По своей сути является оберткой для User, 
 * с одним отличием — Person представляет собой незалогиненного пользователя тоже.
 *
 * @author Ilya Doroshin
 */
namespace Core\Model;

class Category extends \ActiveRecord\Model implements Humanizeurl
{
    public $children;
    public $allChildren = array();
    public $selected;
    public $current;
    
    static $breadcrumbs = array();
    static private $_selectCategories = array();
    
    static $has_many = array(
        array('goodcategory'),
        array('categories', 'foreign_key' => 'parent_id'),
        array('goods', 'through' => 'goodcategory')
    );

    static $before_save = array('updateLink');

    public function updateLink()
    {
        $this->link = \Core\Url::encode($this->name);
    }

    public function get_url()
    {
        $url = \Core\Router::get()->generate('shop_category', array('category' => $this->encoded_key));
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
    
    public function show()
    {
        $this->is_visible = 1;
        $this->save();
    }
    
    public function hide()
    {
        $this->is_visible = 0;
        $this->save();
    }
    
    public function getChildren()
    {
        return self::$_selectCategories;
    }
        
    static public function getAll($selected = false)
    {
        $_categories = self::all(array(
            'order' => 'weight desc'
        ));
        
        $address = $categories = array();
        
        foreach ($_categories as &$_category)
        {
//            echo '@ ' . $_category->id . '<br />';
            if ($_category->is_visible)
            {
                if (isset($address[$_category->id]->allChildren) && count($address[$_category->id]->allChildren))
                {
                    $_category->allChildren = array_merge($address[$_category->id]->allChildren, array($_category->id));
                }
                else 
                {
                    $_category->allChildren = array($_category->id);
                }

            }
                
            if (isset($address[$_category->id]))
            {
                if (isset($address[$_category->id]->children))
                {
                    $_category->children = $address[$_category->id]->children;
                    $address[$_category->id] = &$_category;
                }
            }
            else 
            {
                $address[$_category->id] = &$_category;
            }
            
            if ($_category->parent_id)
            {
                if (!isset($address[$_category->parent_id]->children))
                {
                    $address[$_category->parent_id]->children = array();
                }
                if (!isset($address[$_category->parent_id]->allChildren))
                {
                    $address[$_category->parent_id]->allChildren = array();
                }
                if ($_category->is_visible)
                {
                    $address[$_category->parent_id]->allChildren[] = $_category->id;
                }
                array_push($address[$_category->parent_id]->children, &$_category);
            }
            else 
            {
                array_push($categories, &$_category);
            }
        }
        
        if (isset($address[$selected]))
        {
            self::$_selectCategories = $address[$selected]->allChildren;
        }
        
        self::_recursive($selected, $address, true);
                
        return $categories;
    }
    
    private function _recursive($id, $address, $current = false)
    {
        if (isset($address[$id]))
        {
            $array = array(
                'id'   => $address[$id]->id,
                'name' => $address[$id]->name,                
            );
            
            $address[$id]->selected     = true;
            
            if ($current) 
            {
                $array['current']       = true;
                $address[$id]->current  = true;
            }
            
            array_unshift(self::$breadcrumbs, $array);            
            
            if($address[$id]->parent_id)
                self::_recursive($address[$id]->parent_id, $address);
                
        }
    }
}