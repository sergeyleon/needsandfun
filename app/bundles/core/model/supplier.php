<?php

namespace Core\Model;

class Supplier extends \ActiveRecord\Model // implements Itemswithpics
{
    static $has_many = array(
        array('goods')
    );
    
    public function getBindId()
    {
        return $this->id;
    }    
    /**
     * генерируем массив с иконками
     
    public function getIcons()
    {
        return $this->brandpictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
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
    }   */ 
}