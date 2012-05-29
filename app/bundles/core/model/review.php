<?php
namespace Core\Model;

class Review extends \ActiveRecord\Model
{
	static $has_one = array(
		array('goodreview',  'class' => 'Goodreview'),
		array('placereview', 'class' => 'Placereview'),
		array('eventreview', 'class' => 'Eventreview'),
	);

	static $belongs_to = array(
		array('client')
	);

	static function add(Reviewable $item, Client $client, $values)
	{		
		$review = new self();
		$review->setClient($client);

		$review->rating      = $values['rate'];

		if (!empty($values['pros']))
		{
			$review->pros 	 = $values['pros'];
		}

		if (!empty($values['contras']))
		{
			$review->contras = $values['contras'];
		}

		if (!empty($values['comment']))
		{
			$review->comment = $values['comment'];
		}

		$review->save();

		$link 			 = $item->getLinkModel();
		$link->item_id   = $item->id;
		$link->review_id = $review->id;
		$link->save();

		return $review;
	}

	public function remove()
	{
		$item = $this->element()->item;
		$this->delete();
		$item->updateRating();
	}

	public function confirm()
	{
		$this->is_checked = 1;
		$this->save();
		$this->element()->item->updateRating();
	}

	public function setClient(Client $client)
	{
		$this->client_id = $client->id;
		$this->save();
	}

	public function element()
	{
		$result = false;
		if ($this->goodreview)
		{
			$result = $this->goodreview;
		}
		else if ($this->placereview)
		{
			$result = $this->placereview;
		}
		else if ($this->eventreview)
		{
			$result = $this->eventreview;
		}
		return $result;
	}

	static function actual(Client $client = null, $last = false)
	{
		$options = array(
			'conditions' => array('is_checked=1')
		);

		if ($client)
		{
			$options['conditions'][0] .= ' and client_id=?';
			$options['conditions'][] = $client->id;

			if ($client->orders)
			{
				$options['conditions'][0] .= ' and created > ?';
				$options['conditions'][]   = current($client->orders)->created;
			}
		}

		return self::all($options);
	}
}
