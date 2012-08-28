<?php

namespace Managers\Controller;

class Orders extends \Core\Abstracts\Singleton
{
    public function __construct()
    {
        if (isset($_POST['saveOrder']))
        {
            $this->_proceed($_POST);
        }
        else 
        {
            Index::drawMenu();                
        }
    }
    
    public function add()
    {
        $this->_form();
    }

    public function edit($orderId)
    {
        $this->page['item'] = \Core\Model\Order::find($orderId);
        $this->_form();
    }
    
    public function remove($orderId)
    {

        // CHECK AND DELETE
        $delivers = \Core\Model\Delivery::all(array('conditions' => array('order_id = ? ', $orderId)));
        
        foreach ($delivers as $deliver)
        {
          $deliver->delete(); 
        }
        
        // CHECK AND DELETE
        $goods = \Core\Model\Ordergood::all(array('conditions' => array('order_id = ? ', $orderId)));
        
        foreach ($goods as $good)
        {
          $good->delete(); 
        }
        
        // CHECK AND DELETE
        $stats = \Core\Model\Orderstatus::all(array('conditions' => array('order_id = ? ', $orderId)));
        
        foreach ($stats as $stat)
        {
          $stat->delete(); 
        }
    
    
        \Core\Model\Order::find($orderId)->delete();
        $this->router->go($this->router->generate('manage_orders_index'));        
    }
    
    public function index()
    {
        $this->page['items']    = \Core\Model\Order::getAll();
        $this->page['statuses'] = \Core\Model\Status::getAll();
        $this->page->display('orders/index.twig');
    }
    
    private function _form()
    {
        $this->page['statuses'] = \Core\Model\Status::getAll();
        $this->page->display('orders/form.twig');
    }
    
    private function _proceed($values)
    {
        $item = empty($values['id'])
            ? new \Core\Model\Order()
            : \Core\Model\Order::find($values['id']);
        
        if ($values['status'] != $item->getStatus()->id)
        {
            $item->setStatus($values['status']);
        }
        
        $item -> save();

        $this->router->go($this->router->generate('manage_orders_index'));
    }
}