<?php

namespace Core\Model;

class Goodcategory extends \ActiveRecord\Model
{
    static $table = 'goods_categories';
    static $belongs_to = array(
        array('category'),
        array('good')
    );
}