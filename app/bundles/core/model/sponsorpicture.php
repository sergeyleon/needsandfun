<?php

namespace Core\Model;

class Sponsorpicture extends \ActiveRecord\Model implements Picsforitem
{
    static $table = 'sponsor_pictures';
    static $belongs_to = array(
        array('picture'),
        array('sponsor')
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