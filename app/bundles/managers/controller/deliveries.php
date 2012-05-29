<?php

namespace Managers\Controller;

class Deliveries extends \Core\Abstracts\Singleton
{
    public function __construct()
    {
        if (isset($_POST['proceed']))
        {
            $this->_proceed($_POST);
        }
        else 
        {
            Index::drawMenu();                
        }
    }

    public function index()
    {
        $this->page['items']    = \Core\Model\Delivery::getCurrent();
        $this->page->display('deliveries/index.twig');
    }
    
    public function add()
    {
        $this->_form();
    }

    public function edit($deliveryId)
    {
        $this->page['item'] = \Core\Model\Delivery::find($deliveryId);
        $this->_form();
    }

    public function finish($deliveryId)
    {
        \Core\Model\Delivery::find($deliveryId)->finish();
        $this->router->go($this->router->generate('manage_deliveries_index'));
    }

    public function undo($deliveryId)
    {
        \Core\Model\Delivery::find($deliveryId)->undo();
        $this->router->go($this->router->generate('manage_deliveries_index'));
    }
    
    public function remove($orderId)
    {
        \Core\Model\Order::find($orderId)->delete();
        $this->router->go($this->router->generate('manage_orders_index'));        
    }
    
    
    private function _form()
    {
        $this->page['types']      = \Core\Model\Deliverytype::getAll();
        $this->page['metrolines'] = \Core\Model\Metroline::getAll();
        $this->page->display('deliveries/form.twig');
    }
    
    private function _proceed($values)
    {
        $item = empty($values['id'])
            ? new \Core\Model\Delivery()
            : \Core\Model\Delivery::find($values['id']);
        
        
        $item->metro_id = !empty($values['metro'])
            ? $values['metro']
            : null;
        
        if (!empty($values['delivery_date']))
        {
            $item->delivery_date    = new \DateTime($values['delivery_date']);
        }
        
        $item->comment          = $values['comment'];
        $item->address          = $values['address'];
        $item->recall           = $values['recall'];
        $item->delivery_type_id = $values['type'];
        $item->save();
        
        $this->router->go($this->router->generate('manage_deliveries_index'));
    }
}