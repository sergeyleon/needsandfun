<?php 

namespace Core\Model;

class Typeproperty extends \ActiveRecord\Model
{
    static $table = 'type_properties';
    
    static $belongs_to = array(
        array('property'),
        array('type')
    );
}
