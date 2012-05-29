<?php
/**
 * @author Ilya Doroshin
 */
 
namespace Core\Model;

class Articlecategory extends \ActiveRecord\Model
{    
	static $table = 'article_categories';
    static $has_many = array(
        array('articles')
    );

    static function getAll()
    {
    	$options = array(
    		'conditions' => 'deleted is null',
    		'order' 	 => 'name'
    	);

    	return self::all($options);
    }
}
