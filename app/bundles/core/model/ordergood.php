<?php 

namespace Core\Model;

class Ordergood extends \ActiveRecord\Model
{
    static $table = 'order_goods';
    
    static $belongs_to = array(
        array('order'),
        array('size')
    );

    static function getAllByOrder(Order $order)
    {
    	$options = array(
    		'conditions' => array('order_id=?', $order->id)
    	);

    	return self::all($options);
    }
}
