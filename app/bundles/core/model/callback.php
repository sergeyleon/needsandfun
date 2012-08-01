<?php
/**
 * @author Ilya Doroshin
 */
 
namespace Core\Model;

class Callback extends \ActiveRecord\Model
{    
	static $table = 'callbacks';

    static function getAll()
    {
    	$options = array(
            'conditions' => '(date(is_called) = date(now()) or is_called is NULL)',
    		'order' 	 => 'is_called, call_at'
    	);

    	return self::all($options);
    }

    static function create($phone, \DateTime $dt)
    {
        $callback = new self();
        $callback->phone = $phone;
        $callback->call_at = $dt;
        $callback->save();
        
        
    }
}
