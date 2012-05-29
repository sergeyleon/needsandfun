<?php

namespace Core\Model;

class Size extends \ActiveRecord\Model
{
    static $table = 'sizes';
    static $belongs_to = array(
        array('good')
    );
    
    public function getDiscountedPrice()
    {
        return $this->price * (100 - $this->good->discount)/100;
    }
}