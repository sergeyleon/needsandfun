<?php

namespace Core\Model;

class Brandpicture extends \ActiveRecord\Model implements Picsforitem
{
    static $table = 'brand_pictures';
    static $belongs_to = array(
        array('picture'),
        array('brand')
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