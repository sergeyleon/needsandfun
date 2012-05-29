<?php 

namespace Core\Model;

class Impervious extends \ActiveRecord\Model 
{
	static function find($options)
	{
		print_r($options);
		return parent::find($options);
	}	
}