<?php

namespace Core\Model;

class Pagetype extends \ActiveRecord\Model
{
    static $DELIVERY  = 1;
    static $PAYMENT   = 2;    
    static $SIZECHART = 3;    
    static $ABOUT     = 4;
    static $INFOPAGE  = 5;
    
    static $table = 'page_types';
    static $has_many = array(
        array('pages', 'foreign_key' => 'page_type_id')
    );
}