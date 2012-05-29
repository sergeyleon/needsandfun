<?php

namespace Core\Model;

class Author extends \ActiveRecord\Model implements Itemswithpics
{
    static $has_many = array(
        array('authorpictures', 'order' => 'weight', 'foreign_key' => 'item_id'),
        array('articles')
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
        return $this->authorpictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }
    
    public function getMaxPicWeight() 
    {
        $weight = Authorpicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }    

    static function getAll()
    {
        $options = array(
            'conditions' => 'deleted is null',
            'order'      => 'name'
        );

        return self::all($options);
    }    
}