<?php

namespace Needsandfun\Controller;

class Email extends \Core\Abstracts\Authorized
{
	public function userAdded(\Core\Model\User $user, $options = array())
	{
		$options = array(
			'login' 	  => $user->login,
			'password'    => empty($options['password'])
				? ''
				: $options['password'],
			'client'	  => $user->client,
			'key'		  => $user->getActivateKey(),
			'host'		  => $this->router->getHost()
		);

		$this->env->setByNamespace('Needsandfun');

		\Core\Model\Email::get()->create(array(
            'to'      => $user->login,
            'subject' => 'Вы успешно зарегистрировались на сайте needsandfun.ru',
            'text'    => $this->page->render('email/user/confirm.twig', $options)
        ));
	}

	public function activateAccount(\Core\Model\User $user)
	{
		$options = array(
			'client'	  => $user->client,
			'login' 	  => $user->login,
			'key'		  => $user->getActivateKey(),
			'host'		  => $this->router->getHost()
		);

		\Core\Model\Email::get()->create(array(
            'to'      => $user->login,
            'subject' => 'Необходимо подтвердить создание аккаунта на сайте needsandfun.ru',
            'text'    => $this->page->render('email/user/activation.twig', $options)
        ));
	}	

	public function confirmFlushPassword(\Core\Model\User $user)
	{
		$options = array(
			'client'	  => $user->client,
			'login' 	  => $user->login,
			'newPassword' => $user->updateNewPassword(),
			'key'		  => $user->getConfirmKey(),
			'host'		  => $this->router->getHost()
		);

		\Core\Model\Email::get()->create(array(
            'to'      => $user->login,
            'subject' => 'Восстановление пароля на сайте needsandfun.ru',
            'text'    => $this->page->render('email/user/forgot.twig', $options)
        ));
	}

	public function confirmOrder(\Core\Model\Order $order,$options)
	{
		$options = array(
			'order' => $order,
			'host'    => $this->router->getHost(),
			'delivery' => $options['delivery'],
			'metro' => $options['metro'],
			'file' => $options['file'],
			'discount' => $order->discount
		);

		\Core\Model\Email::get()->create(array(
            'to'      => $order->getClient()->email,
            'from' => 'Needsandfun.ru ',
            'subject' => 'Вы сделали заказ на сайте needsandfun.ru',
            'text'    => $this->page->render('email/order/confirm.twig', $options),
            'file'    => $options['file']
        ));

  }
	public function confirmOrderAdmin(\Core\Model\Order $order,$options)
	{

		$options = array(
			'order' => $order,
			'host'    => $this->router->getHost(),
			'delivery' => $options['delivery'],
			'metro' => $options['metro']
		);
	
		\Core\Model\Email::get()->create(array(
            'to'      => 'info@needsandfun.ru',
            'cc'      => 'shop@needsandfun.ru',
            'from' => 'Needsandfun.ru <'.$order->getClient()->email.'>',
            'subject' => 'Заказ с сайта needsandfun.ru',
            'text'    => $this->page->render('email/order/confirmadmin.twig', $options)
        ));
    
    \Core\Model\Email::get()->create(array(
            'to'      => 'bazzy@yandex.ru',
            'from' => 'Needsandfun.ru <'.$order->getClient()->email.'>',
            'subject' => 'Заказ с сайта needsandfun.ru',
            'text'    => $this->page->render('email/order/confirmadmin.twig', $options)
        ));
      
	}
	
	public function confirmAddEvent($values)
	{
		$options = array(
			'host'    => $this->router->getHost()
		);
		
		\Core\Model\Email::get()->create(array(
            'to'      => 'info@needsandfun.ru',
            'cc'      => 'shop@needsandfun.ru',
            'subject' => 'Событие на сайте',
            'text'    => 'Пользователь добавил событие: '.$values['title']
        ));
		
		\Core\Model\Email::get()->create(array(
            'to'      => 'bazzy@yandex.ru',
            'subject' => 'Событие на сайте',
            'text'    => 'Пользователь добавил событие: '.$values['title']
        ));
	}
	
	public function confirmAddCallback($values)
	{
		$options = array(
			'host'    => $this->router->getHost()
		);
		
		\Core\Model\Email::get()->create(array(
            'to'      => 'info@needsandfun.ru',
            'cc'      => 'shop@needsandfun.ru',
            'subject' => 'Заказ звонка',
            'text'    => 'Пользователь заказал звонок на номер: '.$values
        ));
		
		\Core\Model\Email::get()->create(array(
            'to'      => 'bazzy@yandex.ru',
            'subject' => 'Заказ звонка',
            'text'    => 'Пользователь заказал звонок на номер: '.$values
        ));
        
	}
	

	public function test()
    {
        $this->page['order'] = \Core\Model\Order::find(56);
        $this->page['host'] = 'http://' . $_SERVER['HTTP_HOST'];

        $this->page->display('email/order/confirm.twig');
    }	
}