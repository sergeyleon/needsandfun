<?php

namespace Core\Model;

class Goodproperty extends \ActiveRecord\Model
{
    static $table = 'goods_properties';
    static $belongs_to = array(
        array('good'),
        array('property')
    );
}