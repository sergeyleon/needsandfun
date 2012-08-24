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
	
	
	static function add_private(Reviewable $item, $client = 0, $values)
	{		
	
	   function generate_code() //запускаем    функцию, генерирующую код.
  	 {
  		$hours = date("H"); // час       
  		$minuts = substr(date("H"), 0 , 10);// минута 
  		$mouns = date("m");    // месяц             
  		$year_day = date("z"); // день в году 
  		$str = $hours . $minuts . $mouns . $year_day; //создаем строку
  		$str = md5(md5($str)); //дважды шифруем в md5
  		$str = strrev($str);// реверс строки
  		$str = substr($str, 3, 6); // извлекаем 6 символов, начиная с 3
  		// Вам конечно же можно постваить другие значения, так как, если взломщики узнают, каким именно способом это все генерируется, то в защите не будет смысла.
  		$array_mix = preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
  		srand ((float)microtime()*1000000);
  		shuffle ($array_mix);
  
  		return implode("", $array_mix);
  	}
    function chec_code($code) //проверяем код 
  	{
  		$code = trim($code);//удаляем пробелы 
  		$array_mix = preg_split ('//', generate_code(), -1, PREG_SPLIT_NO_EMPTY);
  		$m_code = preg_split ('//', $code, -1, PREG_SPLIT_NO_EMPTY);
  		$result = array_intersect ($array_mix, $m_code);
  		if (strlen(generate_code())!=strlen($code)) {
  			return false;
  		}
  		if (sizeof($result) == sizeof($array_mix)) {
  			return true;
  		}
  		else {
  			return false;
  		}
  	}

	
		$review = new self();
		$review->client_id = 0;

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

    if (!chec_code($values['code']))
  	{
  		return false;
  	}
    else {
  		$review->save();
  
  		$link 			 = $item->getLinkModel();
  		$link->item_id   = $item->id;
  		$link->review_id = $review->id;
  		$link->save();
  
  		return $review;
		}
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
