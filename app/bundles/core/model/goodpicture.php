<?php

namespace Core\Model;

class Goodpicture extends \ActiveRecord\Model implements Picsforitem
{
    static $table = 'good_pictures';
    static $belongs_to = array(
        array('picture'),
        array('good')
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