<?php

namespace Core\Model;

class Placereview extends \ActiveRecord\Model implements Reviewablelink
{
	static $table = 'place_reviews';
	static $belongs_to = array(
		array('review'),
		array('place', 'foreign_key' => 'item_id')
	);

	public function getName()
	{
		return 'place';
	}	

	public function getLink()
	{
		return $this->place->url;
	}

	public function get_item()
	{
		return $this->place;
	}
}
