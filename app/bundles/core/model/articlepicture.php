<?php

namespace Core\Model;

class Articlepicture extends \ActiveRecord\Model implements Picsforitem
{
    static $_WIDTH  = 300;
    static $_HEIGHT = 300;

    static $table = 'article_pictures';
    static $belongs_to = array(
        array('picture'),
        array('article')
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