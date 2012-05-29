<?php
/**
 * Модель корзины.
 * @author Ilya Doroshin
 */
namespace Core\Model;

class Cart extends \Core\Abstracts\Singleton
{    
    /**
     * имя ключа в хранилище
     */
    static $KEY      = 'orderId';
    
    /**
     * текущий заказ
     */
    private $_storage = false;
    private $_order   = false;
    private $_client  = false;    
    private $_items   = false;
    
    public function getSize()
    {
        return $this->_order
            ? $this->_order->getItemsQnt()
            : 0;
    }
    
    public function getGoods()
    {
        $order = $this->_getOrder();
        $goods = array();        
        
        if ($order)
        {
            foreach ($order->ordergoods as $ordergood)
            {
                if (empty($goods[$ordergood->size_id]))
                {
                    $goods[$ordergood->size_id] = array(
                        'good'    => $ordergood->size->good,
                        'size'    => $ordergood->size,
                        'showSize' => count($ordergood->size->good->sizes),
                        'perGood' => $ordergood->size->getDiscountedPrice(),
                        'qnt'     => 0,
                        'price'   => 0
                    );
                }
                
                $goods[$ordergood->size_id]['qnt']++;
                $goods[$ordergood->size_id]['price'] += $ordergood->size->getDiscountedPrice();
            }
        }
        
        return $goods;
    }
    
    public function getPrice()
    {
        return $this->_order
            ? $this->_order->getSum()
            : 0;
    }

    public function getClient()
    {
        return $this->_client;
    }

    public function getUser()
    {
        return $this->_client->user;
    }

    public function getOrder()
    {
        return $this->_order;
    }
    
    public function addGood($size, $qnt)
    {
        if ($this->_getOrder(true))
        {
            $size = Size::find($size);
            
            for ($a=0; $a < $qnt; $a++)
            {
                $this->_getOrder(true)->addGood($size);
            }
        }

        $this->_returnJson();
    }
    
    public function removeGood($size, $qnt)
    {
        if ($this->_getOrder(true))
        {
            $order = $this->_getOrder();
            $ordergoods = \Core\Model\Ordergood::all(array(
                'conditions' => array('order_id=? and size_id=?', $order->id, $size),
                'limit'      => $qnt
            ));
            
            foreach ($ordergoods as $ordergood) 
            {
                $ordergood->delete();
            }
        }
        
        $this->_returnJson();
    }    
    
    private function _returnJson()
    {
        $result = array(
            'total' => $this->getSize(),
            'price' => $this->getPrice()
        );
        
        echo json_encode($result);
    }
    
    private function _getOrder($create = false) 
    {
        if (!$this->_order && $create)
        {
            $this->_order  = new Order();
            $this->_order->save();
            $this->_storage->setValue(self::$KEY, $this->_order->id);
        }
        else 
        {
            $client = \Core\Abstracts\Authorized::get()->getClient();
            if ($this->_order && $client)
            {
                $this->_order->setClient($client);
            }
        }
        
        return $this->_order;
    }
    
    private function _getClient($create = false) 
    {
        if (!$this->_client && $create)
        {
            $this->_client = Client::prepare();
        }
        return $this->_client;
    }
    
    public function confirm($data)
    {
        // $data['goods'] # массив со значениями товаров
        if ($this->_order)
        {
            $deliveryType = $data['deliveryType'];

            $values = array(
                'phone' => $data['delivery'][$deliveryType]['phone'],
                'email' => $data['delivery'][$deliveryType]['email']
            );
            
            $client = \Core\Abstracts\Authorized::get()->getClient();

            if (!$client)
            {
                $user = User::register($values['email']);

                if ($user)
                {
                    $client = $user->client;
                }
            }

            $this->_order->setDiscount($client->discount['reviews']['discount'], $client->discount['summ']['discount']);

            if ($client)
            {
                $this->_order->setClient($client);
                $this->_order->setStatus(Status::FRESH);
                
                $delivery = array(
                    'order' => $this->_order,
                    'type'  => $deliveryType,
                    'data'  => $data['delivery'][$deliveryType]
                );
                
                Delivery::prepare($delivery);
            }
            else
            {
                return false;
            }
        }

        return $this->_order;
    }
    
    public function __construct(\Core\Storage\Common $storage)
    {
        $this->_storage = $storage;
        
        if ($storage->getValue(self::$KEY))
        {
            $options = array(
                'conditions' => array(
                    'id=? and id not in (select order_id from order_statuses)', $storage->getValue(self::$KEY)
            ));
            
            $this->_order = Order::find($options);

            if ($this->_order)
            {
                $this->_client = $this->_order->client;
            }
        }
    }
    
    static function init(\Core\Storage\Common $storage)    
    {
        $cart = self::get($storage);
        return $cart;
    }
}
