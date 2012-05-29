<?php

namespace Core\Model;

class Banner extends \ActiveRecord\Model implements Itemswithpics
{
    static $has_many = array(
        array('bannerpictures', 'order' => 'weight', 'foreign_key' => 'item_id')
    );

    static $belongs_to = array(
        array('type', 'class' => 'Bannertype', 'foreign_key' => 'type_id')
    );

    static function big()
    {
        # ключ: main
        return self::getAll(array('type' => 'main'));
    }

    static function todayInShop()
    {
        # ключ: main
        return self::getAll(array('type' => 'today', 'order' => 'rand()', 'func' => 'find'));
    }

    static function shop($limit = 3)
    {
        # ключ: main
        return self::getAll(array('type' => 'shop', 'order' => 'rand()', 'limit' => $limit));
    }        

    static function getAll($_options = false)
    {
        $options = array(
            'conditions' => array('banners.deleted is null'),
            'order' => 'type_id, title'
        );

        if (!empty($_options['type']))
        {
            $types = ' and banner_types.key in (?)';
            $options['conditions'][0] .= $types;
            $options['conditions'][] = $_options['type'];
            $options['joins'] = array('type');
        }

        if (!empty($_options['order']))
        {
            $options['order'] = $_options['order'];
        }

        if (!empty($_options['limit']))
        {
            $options['limit'] = $_options['limit'];
        }        

        $func = empty($_options['func'])
            ? 'all'
            : $_options['func'];

        return self::$func($options);
    }
    
    public function getBindId()
    {
        return $this->id;
    }    

    public function getIcons()
    {
        return $this->bannerpictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }
    
    public function getMaxPicWeight() 
    {
        $weight = Bannerpicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }    
}