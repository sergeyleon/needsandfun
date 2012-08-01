<?php

namespace Core\Model;

class Brand extends \ActiveRecord\Model implements Itemswithpics
{
    static $perPage = 24;
    
    static $has_many = array(
        array('brandpictures', 'order' => 'weight', 'foreign_key' => 'item_id'),
        array('goods')
    );
    
    public function getBindId()
    {
        return $this->id;
    }    
    /**
     * генерируем массив с иконками
     */
    public function getIcons()
    {
        return $this->brandpictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }
    
    
    static function getPager($page, $brand = false, $route, $total = 0)
    {
        $options = array();
        $routeParams['current'] = $page;

        if ($brand)
        {
            $routeParams['brand'] = $brand;
            $options['brand'] = $brand;
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
    
    
    public function getMaxPicWeight() 
    {
        $weight = Brandpicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }    
}