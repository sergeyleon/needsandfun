<?php

namespace Core\Abstracts;

class Authorized extends Singleton
{
	private $_user  = false;
	private $_token = false;
	private $_cart  = false;

	protected function __construct()
	{
		parent::__construct();

		$token = \Core\Model\Client::authorized($this->cookieStorage);
		$this->_cart = \Core\Model\Cart::init($this->sessionStorage);

		if ($token)
		{
			$this->_token = $token;
			$this->_user  = $token->user;
		}

		$this->page['loggedUser'] = $this->_user;
		$this->page['cart'] 	  = $this->_cart;
	}

	public function getCart()
	{
		return $this->_cart;
	}

	public function getUser()
	{
		return $this->_user;
	}	

	public function getClient()
	{
		$client = $this->_user
			? $this->_user->client
			: false;

		return $client;
	}

	public function getToken()
	{
		return $this->_token;
	}
}