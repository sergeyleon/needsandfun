<?php

namespace Core\Model;

class Deliverytype extends \ActiveRecord\Model
{
    /**
     * айди типов в базе
     */
    static $courier = 1;
    static $pickup  = 2;
    static $metro   = 3;

    static $table = 'delivery_types';
    static $has_many = array(
        array('deliveries')
    );

    static function getAll()
    {
        $options = array(
            'conditions' => 'deleted is null'
        );

        return self::all($options);
    }
}