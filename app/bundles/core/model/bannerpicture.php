<?php

namespace Core\Model;

class Bannerpicture extends \ActiveRecord\Model implements Picsforitem
{
    static $_WIDTH  = 250;
    static $_HEIGHT = 150;

    static $table = 'banner_pictures';
    static $belongs_to = array(
        array('picture'),
        array('banner')
    );
    
    static function updateWeights($ids)
    {
        foreach ($ids as $weight => $id)
        {
            $picture = self::find($id);
            $picture->weight = $weight;
            $picture->save();
        }
    }
    
    static function create()
    {
        return new self();
    }
}