<?php

namespace Core\Model;

class Partnerpicture extends \ActiveRecord\Model implements Picsforitem
{
    static $table = 'partner_pictures';
    static $belongs_to = array(
        array('picture'),
        array('partner')
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