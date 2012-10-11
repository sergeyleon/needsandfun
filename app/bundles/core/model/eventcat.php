<?php 

namespace Core\Model;

class Eventcat extends \ActiveRecord\Model
{
    static $table = 'event_cats';
    
    static $belongs_to = array(
        array('event', 'foreign_key' => 'event_id'),
        array('eventcategory')
    );
}
