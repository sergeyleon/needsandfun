<?php

namespace Core\Model;

class Eventreview extends \ActiveRecord\Model implements Reviewablelink
{
	static $table = 'event_reviews';
	static $belongs_to = array(
		array('review'),
		array('event', 'foreign_key' => 'item_id')
	);

	public function getName()
	{
		return 'event';
	}

	public function getLink()
	{
		return $this->event->url;
	}

	public function get_item()
	{
		return $this->event;
	}
}
