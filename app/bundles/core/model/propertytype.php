<?php 

namespace Core\Model;

class Propertytype extends \ActiveRecord\Model
{
    static $table = 'property_types';
    
    static $belongs_to = array(
        array('property')
    );
}
