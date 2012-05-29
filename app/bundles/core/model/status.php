<?php

namespace Core\Model;

class Status extends \ActiveRecord\Model
{
    const FRESH = 1;

    static $has_many = array(
        'orderstatuses'
    );
    
    static function create(Order $order, $status)
    {   
        $orderStatus = new Orderstatus();
        $orderStatus->order_id  = $order->id;
        $orderStatus->status_id = $status;
        $orderStatus->save();
    }

    public function get_title()
    {
        return $this->name;
    }

    static function getAll($except = false)
    {
        $options = array(
            'order' => 'value'
        );

        if ($except)
        {
            $options['conditions'] = array(
                'id not in (?)', $except
            );
        }

        return self::all($options);

    }

    static function getActual(Order $order)
    {
        $options = array(
            'conditions' => array('order_id=?', $order->id),
            'order' => 'created desc',
            'limit' => '1'
        );

        return Orderstatus::find($options)->status;
    }
}