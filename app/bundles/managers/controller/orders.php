<?php

namespace Managers\Controller;

class Orders extends \Core\Abstracts\Singleton
{
    public function __construct()
    {
        if (isset($_POST['deleteItem']))
        {
            $this->_deleteItem($_POST);
        }
        
        if (isset($_POST['addItem']))
        {
            $this->_addItem($_POST);
        }
        
        if (isset($_POST['inOrder']))
        {
            $this->_inOrder($_POST);
        }
        
    
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
    
    
    public function findNewItem($orderId)
    {

      if(isset($_POST['query'])) { 
        $query = $_POST['query'];
        $this->page['goods'] = \Core\Model\Good::all(array('conditions' => array('(name like ? or article like ? or description like ?) and deleted is null', '%'.$query.'%', '%'.$query.'%', '%'.$query.'%' ), 

             ));
      }  
      
      $this->page['order'] = $orderId;
      $this->page->display('orders/find.twig');
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
        
        $this->page['types']      = \Core\Model\Deliverytype::getAll();
        $this->page['metrolines'] = \Core\Model\Metroline::getAll();
        
        $this->page->display('orders/form.twig');
    }
    
    
    private function _deleteItem($values)
    {
    
      $price = $values['price'];
      
      $goods = \Core\Model\Ordergood::find($values['deleteItem']);
      $orderId = $goods->order_id;
      $sizeId = $goods->size_id;
      $goods->delete();


      $sizes = \Core\Model\Size::find($sizeId);
      $goodId = $sizes->good_id;
      $goodPrice = $sizes->price;
      
      $good = \Core\Model\Good::find($goodId);
      
      if(!empty($good->discount) and $good->discount != 0) {
        $price = $$goodPrice*$good->discount/100;
      }
      else { $price =  $goodPrice; }
      
      $order = \Core\Model\Order::find($orderId);
      $price = $order->price-$price;
      
      $order->price = $price;
      
      $order->save();
      
      $check = \Core\Model\Ordergood::all(array('conditions' => array('order_id = ? ', $orderId)));
      if(count($check) == 0) {
        $this->remove($orderId);
      }
      else {
        $this->router->reload();
      }
    }
    
    private function _addItem($values)
    {
      
      $goods = \Core\Model\Ordergood::find($values['addItem']);
      
      $good = new \Core\Model\Ordergood();
      
      $good->order_id      = $goods->order_id;
      $good->size_id       = $goods->size_id;
      $good->price         = $goods->price;
      
      $good->save();
      
      $orderId =  $goods->order_id;
      $sizes = \Core\Model\Size::find($goods->size_id);
      $goodId = $sizes->good_id;
      $goodPrice = $sizes->price;
      
      $good = \Core\Model\Good::find($goodId);
      
      if(!empty($good->discount) and $good->discount != 0) {
        $price = $goodPrice*$good->discount/100;
      }
      else { $price = $goodPrice; }
      
      $order = \Core\Model\Order::find($orderId);
      $price = $order->price+$price;
      
      $order->price = $price;
      
      $order->save();
      
      
      $this->router->reload();
      
    }
    
    private function _inOrder($values)
    {

      
      $goods = \Core\Model\Size::find($values['inOrder']);
      
      $good = new \Core\Model\Ordergood();
      
      $good->order_id      = $values['orderId'];
      $good->size_id       = $goods->id;
      $good->price         = $goods->price;
      
      $good->save();
      
      
      $goodId = $goods->good_id;
      $goodPrice = $goods->price;
      
      $good = \Core\Model\Good::find($goodId);
      
      if(!empty($good->discount) and $good->discount != 0) {
        $price = $goodPrice*$good->discount/100;
      }
      else { $price = $goodPrice; }
      
      $order = \Core\Model\Order::find($values['orderId']);
      $price = $order->price+$price;
      
      $order->price = $price;
      
      $order->save();
      
      $this->router->go($this->router->generate('manage_orders_index'));
      //$this->router->reload();
      
    }
    
    private function _proceed($values)
    {

        $item = empty($values['id'])
            ? new \Core\Model\Order()
            : \Core\Model\Order::find($values['id']);
        
        $delivery = \Core\Model\Delivery::all(array('conditions' => array('order_id = ? ', $item->id )));
        
        $delivery = $delivery[0];
        
        if($values['delivery_price'] == '') { $values['delivery_price'] = null; }
        $delivery->delivery_price = $values['delivery_price'];
        
        $delivery->metro_id = !empty($values['metro'])
            ? $values['metro']
            : null;
        
        if (!empty($values['delivery_date']))
        {
            $delivery->delivery_date    = new \DateTime($values['delivery_date']);
        }
        
        $delivery->comment          = $values['comment'];
        $delivery->address          = $values['address'];
        $delivery->recall           = $values['recall'];
        $delivery->delivery_type_id = $values['type'];
        
        $delivery -> save();

        foreach ($item->orderstatuses as $orderStatus)
        {
            $orderStatus->delete();    
        }
        
        if ($values['status'] != $item->getStatus()->id)
        {
            $item->setStatus($values['status']);
        }
        $item -> price = $values['price']+$values['delivery_price'];
        $item -> save();
        

        $this->router->go($this->router->generate('manage_orders_index'));
    }
}