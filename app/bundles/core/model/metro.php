<?php
/**
 * @author Ilya Doroshin
 */
 
namespace Core\Model;

class Metro extends \ActiveRecord\Model
{    
    static $belongs_to = array(
        array('metroline')
    );

    static function getAll()
    {
    	$options = array(
    		'conditions' => array('deleted is null 
    					 and metroline_id not in (select id from metro_lines where deleted is not null)'),
    		'order' => 'name'
    	);

    	return self::all($options);
    }
}
