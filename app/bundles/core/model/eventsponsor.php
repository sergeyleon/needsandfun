<?php 

namespace Core\Model;

class Eventsponsor extends \ActiveRecord\Model
{
    static $table = 'event_sponsors';
    
    static $belongs_to = array(
        array('event'),
        array('sponsor')
    );
}
