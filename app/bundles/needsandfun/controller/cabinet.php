<?php

namespace Needsandfun\Controller;

class Cabinet extends \Core\Abstracts\Authorized
{
	protected function __construct()
	{
		parent::__construct();

		if (!$this->getUser())
		{
			$this->router->go($this->router->generate('index'));
		}

		if (!empty($_POST['saveProfile']))
		{
			$this->_updateProfile($_POST);
		}
		else if (isset($_POST['addEvent']))
		{
			$this->addEvent($_POST);
		}
		else
		{
			$this->page['current_route'] = $this->router->getRoute();
		}
	}

	public function logout()
	{
		$user = $this->getUser();

		if ($user) {
			$user->logout($this->getToken());
			$this->page->setMessage('Выход из личного кабинета произведен успешно!');
		}

		$this->router->go($this->router->generate('index'));
	}

	public function activateAccount($key)
    {
        $user = \Core\Model\User::activateUser($key);

        if ($user)
        {
            $this->page->setMessage('Аккаунт подтвержден успешно!');
        }
        else
        {
            $this->page->setMessage('Ошибка активации!');   
        }

        $this->router->go($this->router->generate('index'));
    }  

	public function addEvent($values)
	{
		if (\Core\Model\Event::add($values))
		{
		
		  
			$this->page->setMessage('Объявление добавлено успешно! В ближайшее время наши менеджеры свяжутся с вами для уточнения указанной информации.');
			
			Email::confirmAddEvent($values);
			
			$this->router->go($this->router->generate('cabinet_index'));
		}
		else
		{
			$this->page->setMessage('К сожалению, гости сайта не могут размещать объявления — Вам необходимо зарегистрироваться!');
			$this->router->reload();
		}
	}

	private function _updateProfile($values)
	{
		if ($this->getUser()->updateProfile($values))
		{
			$this->router->reload();
		}
	}

	public function index()
	{
		$client = $this->getClient();
		$this->page['currentEvents'] = \Core\Model\Event::currentEvents($client);
		$this->page['calendar'] 	 = $client->calendar;

		$this->page->display('cabinet/index.twig');
	}

	public function orders()
	{
		$this->page['orders'] = $this->getClient()->orders;
		$this->page->display('cabinet/orders.twig');
	}	
	
	public function profile()
	{
		$this->page['client'] = $this->getClient();
		$this->page->display('cabinet/profile.twig');
	}

	public function discounts()
	{
		$this->page['client'] = $this->getClient();
		$this->page->display('cabinet/discounts.twig');
	}

	public function event()
	{
		$this->page['categories'] = \Core\Model\Eventcategory::getAll();
		$this->page->display('cabinet/event.twig');
	}	
}