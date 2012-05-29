<?php

namespace Core\Model;

class Bannertype extends \ActiveRecord\Model
{
    static $table    = 'banner_types';
    static $has_many = array(
        array('banners')
    );    

    static function getAll()
    {
        $options = array(
            'conditions' => 'deleted is null',
            'order' => 'name'
        );

        return self::all($options);
    }    
}