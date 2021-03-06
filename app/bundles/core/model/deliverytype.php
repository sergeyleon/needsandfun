<?php

namespace Core\Model;

class Deliverytype extends \ActiveRecord\Model
{
    /**
     * айди типов в базе
     */
    static $courier = 1;
    static $metro   = 2;
    static $pickup  = 3;
    static $ems     = 4;

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