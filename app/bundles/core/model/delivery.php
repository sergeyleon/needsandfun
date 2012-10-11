<?php

namespace Core\Model;

class Delivery extends \ActiveRecord\Model
{
    static $belongs_to = array(
        array('order'),
        array('metro'),
        array('type', 'class' => 'Deliverytype', 'foreign_key' => 'delivery_type_id')
    );    

    public function getType() 
    {
        return $this->type;
    }

    public function is_metro()
    {
        return $this->delivery_type_id == Deliverytype::$metro;
    }

    public function finish()
    {
        $this->finished = new \DateTime();
        $this->save();
    }

    public function undo()
    {
        $this->finished = null;
        $this->save();
    }

    /**
     * возвращаем текущие доставки
     */
    static function getCurrent()
    {
        $options = array(
            'conditions' => 'finished is null or finished < date_add(now(), interval 6 hour)',
            'order'      => 'finished, order_id desc'
        );

        return self::all($options);
    }

    public function setType($type)
    {
        $this->delivery_type_id = Deliverytype::$$type;
        $this->save();

        $this->order->updateTotalPrice($this->type->price);
    }
    
    static function prepare($options)
    {
        $delivery = new self();
        
        $order              = $options['order'];
        $delivery->order_id = $order->id;
        
        if ('ems' == $options['type'])
        {
          $delivery->delivery_price = $options['data']['price'];
        }

        
        if ('courier' == $options['type'] or 'ems' == $options['type'])
        {
            $data = $options['data'];
            $address = array();
            
            if ($data['city'])
            {
                $address[] = $data['city'];
            }
            
            if ($data['street'])
            {
                $address[] = $data['street'];
            }
            
            if ($data['house'])
            {
                $address[] = 'дом ' . $data['house'];
            }
            
            if ($data['building'])
            {
                $address[] = 'корп./стр.' . $data['building'];
            }
            
            if ($data['appartments'])
            {
                $address[] = 'кв. ' . $data['appartments'];
            }
            
            $delivery->address = implode(', ', $address);
            $delivery->delivery_price = $data['price'];

        } 
        else if ('metro' == $options['type'])
        {
            $delivery->metro_id = $options['data']['metro'];

        }

        if (isset($options['data']['phone']) or isset($options['data']['name']))
        {
            $delivery->recall = $options['data']['phone'].' '.$options['data']['name'];

        }
        
        if (isset($options['data']['comment']))
        {
            $delivery->comment = $options['data']['comment'];
        }

        $delivery->setType($options['type']);
        
        return $delivery;
    }
}