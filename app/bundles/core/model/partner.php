<?php

namespace Core\Model;

class Partner extends \ActiveRecord\Model implements Itemswithpics
{
    static $has_many = array(
        array('partnerpictures', 'order' => 'weight', 'foreign_key' => 'item_id')
    );

    static function banners()
    {
        $result = self::all(array(
            'conditions' => 'deleted is null',
            'limit'      => '5',
            'order'      => 'rand()'
        ));

        return $result;
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
        return $this->partnerpictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }
    
    public function getMaxPicWeight() 
    {
        $weight = Partnerpicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }    
}