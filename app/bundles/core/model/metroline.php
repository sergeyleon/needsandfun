<?php 

namespace Core\Model;

class Metroline extends \ActiveRecord\Model
{
    static $table = 'metro_lines';
    
    static $has_many = array(
        array('metros', 'order' => 'name')
    );

    static function getAll()
    {
    	$options = array(
    		'conditions' => 'deleted is null',
    		'order'		 => 'name'
    	);

    	return self::all($options);
    }
}
