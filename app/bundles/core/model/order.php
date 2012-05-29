<?php 

namespace Core\Model;

class Order extends \ActiveRecord\Model
{
    static $has_many = array(
        array('ordergoods'),
        array('orderstatuses'),
        array('statuses', 'through' => 'orderstatus', 'order' => 'created desc'),
        array('goods', 'through' => 'ordergoods')
    );

    static $has_one = array(
        array('delivery')
    );
    
    static $belongs_to = array(
        array('client')
    );

    public function setDiscount($number, $percent)
    {
        $this->discount = ($this->price - $number) * $percent/100;
        $this->save();
    }
    
    public function getItemsQnt()
    {
        $qnt = $this->getItems()
            ? count($this->getItems())
            : 0;

        return $qnt;
    }

    static public function getAll($statuses = false, $presense = false)
    {
        $options = array(
            'select' => 'orders.*, statuses.value',
            'joins'  => 'inner join (
                select 
                    order_statuses.order_id, statuses.value 
                from order_statuses
                inner join statuses on statuses.id = order_statuses.status_id
                order by order_statuses.created desc) as statuses on statuses.order_id = orders.id',
            'group'  => 'orders.id',
            'order'  => 'statuses.value, orders.created desc'
        );

        $orders = self::all($options);

        if ($statuses)
        {
            if (!is_array($statuses))
            {
                $statuses = array($statuses);    
            }

            foreach ($orders as $key => $order)    
            {
                if ($presense and !in_array($order->value, $statuses) or !$presense and in_array($order->value, $statuses))
                {
                    unset($orders[$key]);
                }
            }
        }
        
        return $orders;
    }
    
    public function getSum()
    {
        $sum = 0;
        foreach ($this->getItems() as $size)
        {
            $sum += $size->price;
        }
        
        return $sum;
    }
    
    public function getItems()
    {
        $items = Ordergood::getAllByOrder($this);
        return $items;
    }
    
    public function addGood(Size $size)
    {
        $orderGood = new Ordergood();
        
        $orderGood->size_id  = $size->id;
        $orderGood->order_id = $this->id;
        $orderGood->price    = $size->getDiscountedPrice();
        $orderGood->save();
        
        $this->updateTotalPrice();
    }
    
    public function updateTotalPrice($add = 0)
    {
        $price = 0;
        foreach ($this->ordergoods as $ordergood)
        {
            $price += $ordergood->price;
        }
        
        $this->price = $price + $add;
        $this->save();
    }

    public function get_discounted_price()
    {
        return $this->price - $this->discount;
    }
    
    public function setStatus($status)
    {
        Status::create($this, $status);
    }

    public function getStatus()
    {
        return Status::getActual($this);
    }    

    public function getClient()
    {
        $client = Client::find($this->client_id);
        return $client;
    }

    public function setClient(Client $client)
    {
        $this->client_id = $client->id;
        $this->save();

    }
}
