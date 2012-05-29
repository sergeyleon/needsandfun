<?php

namespace Core\Model;

class Clientpicture extends \ActiveRecord\Model implements Picsforitem
{
    static $table = 'client_pictures';
    static $belongs_to = array(
        array('picture'),
        array('client')
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