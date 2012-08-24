<?php

namespace Needsandfun\Controller;

class Cart extends \Core\Abstracts\Authorized
{
    public function index()
    {
        if (isset($_POST['cartSubmit']))
        {
            $this->_proceed($_POST);
        }
        else 
        {   
            $this->page['client'] = $this->getClient();
            
            if ($this->getUser())
            {
                $this->page['currentEmail']   = $this->getUser()->login;
            }

            if ($this->getClient())
            {
                $this->page['currentPhone']   = $this->getClient()->phone;
            }
            
            if ($this->getClient())
            {
                $this->page['currentName']   = $this->getClient()->first_name.' '.$this->getClient()->last_name;
            }

            $this->page['shopCategories'] = \Core\Model\Category::getAll();
            
            $this->page['cartDefaults'] = array(
                'pickupAddress' => 'Москва, м. Третьяковская, Б. Толмачевский пер., д. 5, корп. 1, территория института "Гиредмет". Перед приездом за заказом не забудьте убедиться в том, что товар у нас в офисе :)',
                'metrolines'        => \Core\Model\Metroline::all(array('order' => 'name'))
            );


            $this->page['cartDeliveries'] = array(
                'courier' => array(
                    'price' => 300,
                    'text'  => 'Доставка курьером'
                ),
                'metro' => array(
                    'price' => 150,
                    'text'  => 'Доставка к ближайшему метро'
                ),
                'pickup' => array(
                    'price' => 0,
                    'text'  => 'Самовывоз'
                ),
            );
            
            $this->page->display('shop/cart.twig');
        }
    }
    
    public function success() {
      $this->page->display('shop/cart_success.twig');
    }
    
    private function _proceed($values) 
    {

        

        $cart = \Core\Model\Cart::init($this->sessionStorage);
        $order = $cart->confirm($values);
        
        $delivery = \Core\Model\Delivery::all(array('conditions' => array('order_id = ?', $order->id)));
        
        $metro = \Core\Model\Metro::all(array('conditions' => array('id = ?', $delivery[0]->metro_id )));

        if ($order)
        {
            $now = new \DateTime();

            $options = array(
                'orderId' => $order->id,
                'date'    => $now,
                'delivery' => $delivery[0],
                'metro' => $metro[0]->name,
                'email'   => $order->getClient()->getUser()->login
            );

            Email::confirmOrder($order, $options);
            
            Email::confirmOrderAdmin($order, $options);

            $this->page->setMessage('Заказ оформлен успешно! В ближайшее время наши менеджеры свяжутся с вами для подтверждения заказа.');
            $this->router->go($this->router->generate('shop_success'));
        }
        else
        {
            $this->router->reload();
        }
    }
    
    public function update()
    {
        if ('addGood' == $_POST['action'])
        {
            $this->_addGood($_POST['good'], $_POST['qnt']);
        }
        else if ('removeGood' == $_POST['action'])
        {
            $this->_removeGood($_POST['good'], $_POST['qnt']);
        }        
    }
    
    private function _addGood($good, $qnt = 1)
    {
        \Core\Model\Cart::init($this->sessionStorage)->addGood($good, $qnt);
    }
    
    private function _removeGood($good, $qnt = 1)
    {
        \Core\Model\Cart::init($this->sessionStorage)->removeGood($good, $qnt);
    }
    
}