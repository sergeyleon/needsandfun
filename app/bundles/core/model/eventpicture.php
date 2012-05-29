<?php

namespace Core\Model;

class Eventpicture extends \ActiveRecord\Model implements Picsforitem
{
    static $_WIDTH  = 455;
    static $_HEIGHT = 290;
    
    static $table = 'event_pictures';
    static $belongs_to = array(
        array('picture'),
        array('event')
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