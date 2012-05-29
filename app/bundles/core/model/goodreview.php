<?php

namespace Core\Model;

class Goodreview extends \ActiveRecord\Model implements Reviewablelink
{
	static $table = 'good_reviews';	
	static $belongs_to = array(
		array('review'),
		array('good', 'foreign_key' => 'item_id')
	);

	public function getName()
	{
		return 'good';
	}

	public function getLink()
	{
		return $this->good->url;
	}

	public function get_item()
	{
		return $this->good;
	}
}