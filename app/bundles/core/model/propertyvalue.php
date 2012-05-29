<?php 

namespace Core\Model;

class Propertyvalue extends \ActiveRecord\Model
{
    static $table = 'property_values';
    
    static $belongs_to = array(
        array('property')
    );
}
