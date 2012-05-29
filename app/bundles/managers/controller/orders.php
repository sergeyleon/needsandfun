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

        $this->router->go($this->router->generate('manage_orders_index'));
    }
}