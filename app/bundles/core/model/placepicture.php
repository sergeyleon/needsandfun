<?php

namespace Core\Model;

class Placepicture extends \ActiveRecord\Model implements Picsforitem
{
    // static $_WIDTH  = 455;
    // static $_HEIGHT = 290;
    
    static $table = 'place_pictures';
    static $belongs_to = array(
        array('picture'),
        array('place')
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