<?php 

namespace Core\Model;

class Orderstatus extends \ActiveRecord\Model
{
    static $table = 'order_statuses';
    
    static $belongs_to = array(
        array('order'),
        array('status')
    );
}
