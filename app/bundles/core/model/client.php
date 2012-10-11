<?php

namespace Core\Model;

class Client extends \ActiveRecord\Model implements Itemswithpics, Humanizeurl
{
    static $perReview = 50;
    private $_discount = false;

    static $belongs_to = array(
        array('user')
    );
    
    static $has_many = array(
        array('clientpictures', 'order' => 'weight', 'foreign_key' => 'item_id'),
        array('orders', 'order' => 'created desc'),
        array('orderstatuses', 'order' => 'created desc')
    );

    static $before_save = array('updateLink');

    public function updateLink()
    {
        $this->link = \Core\Url::encode($this->name);
    }

    public function get_url()
    {
        $url = \Core\Router::get()->generate('events_reporter', array('reporter' => $this->encoded_key));
        return $url;
    }

    public function get_encoded_key()
    {
        return $this->link;
    }

    static function from_url($link)
    {
        $result = self::find_by_link($link);
        return $result;
    }

    public function get_discount()
    {
        if (!$this->_discount)
        {
            $this->_discount = array(
                'summ'    => array(
                    'orders'   => 0,
                    'discount' => 0,
                    'value'    => 0
                ),
                'reviews' => array(
                    'discount' => 0,
                    'value'    => 0
                )
            );

            $this->_discount['reviews']['value']    = count($this->reviews);
            $this->_discount['reviews']['discount'] = $this->_discount['reviews']['value'] * self::$perReview;

            foreach ($this->orders as $order)
            {
            
            @$statusquery = \Core\Model\Orderstatus::all(array('conditions' => array('order_id = ? and status_id = ?', $order->id, 4)));
              if(count(@$statusquery) > 0) {
               
                  $this->_discount['summ']['orders']++;
                  $this->_discount['summ']['value'] += $order->discounted_price;
               
              }
            }



            $discount = floor($this->_discount['summ']['value']/1000);
            if (5 < $discount) $discount = 5;
           // if($this->emeil == "nomail@nomail.com") $discount = 0;
            $this->_discount['summ']['discount'] = $discount;

        }

        return $this->_discount;
    }

    public function get_reviews()
    {
        return Review::actual($this);
    }

    public function getBindId()
    {
        return $this->id;
    }    
    /**
     * генерируем массив с иконками
     */
    public function getIcons()
    {
        return $this->clientpictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }
    
    public function getMaxPicWeight() 
    {
        $weight = Clientpicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function isMember(Event $event)
    {
        $options = array(
            'client' => $this->id,
            'event'  => $event->id
        );
        
        return count(Member::getAll($options));
    }

    static function getAll()
    {
        $options = array(
            'order'      => 'first_name, last_name',
            'conditions' => 'deleted is null'
        );

        return self::all($options);
    }

    public function get_calendar()
    {
        $calendar = array();
        $events = Event::currentEvents($this);

        $today = new \DateTime();

        if ($events)
        {
            foreach ($events as $event)
            {
                $key = $event->held_date->format('Ym');

                if (!isset($calendar[$key]))
                {
                    $days = $event->held_date->format('t');
                    $start = new \DateTime($event->held_date->format('Y/m/' . '1'));
                    $end = new \DateTime($event->held_date->format('Y/m/' . $days));

                    $page = \Core\Page::get();

                    $calendar[$key] = array(
                        'name'   => $event->held_date->format('n'),
                        'pre'    => (1 - $start->format('N')),
                        'days'   => $event->held_date->format('t'),
                        'after'  => 7 - $end->format('N'),
                        'events' => array()
                    );

                    if ($key == $today->format('Ym'))
                    {
                        $calendar[$key]['today'] = $today->format('j');
                    }
                }

                if (!isset($calendar[$key]['events'][$event->held_date->format('j')]))
                {
                    $calendar[$key]['events'][$event->held_date->format('j')] = array();
                }

                $calendar[$key]['events'][$event->held_date->format('j')][] = $event;
            }
        }

        return $calendar;
    }

    static function authorized(\Core\Storage\Common $storage)
    {
        $token = false;

        if ($storage->getValue(AUTH_TOKEN))
        {
            $user = \Core\Model\User::checkAuth($storage->getValue(AUTH_TOKEN));

            if ($user)
            {
                $token = \Core\Model\Usertoken::getToken($storage->getValue(AUTH_TOKEN));
            }
            else 
            {
                $storage->remove(AUTH_TOKEN);
            }
        }
        return $token;
    }

    static function createProfile(User $user, $options = array())
    {
        $profile = new self();

        $profile->user_id  = $user->id;
        $profile->email    = $user->login;

        $profile->first_name = empty($options['first_name'])
            ? null
            : $options['first_name'];

        $profile->last_name = empty($options['last_name'])
            ? null
            : $options['last_name'];

        $profile->save();
    }
    
    public function get_name()
    {
        $name = array();

        if ($this->first_name)
        {
            array_push($name, $this->first_name);
        }

        if ($this->last_name)
        {
            array_push($name, $this->last_name);
        }        

        $name = implode(' ', $name);

        return $name;
    }
    
    public function setValues($values, $force = false)
    {
        foreach ($values as $name => $value )
        {
            if (isset($this->$name) && (empty($this->$name) || $force))
            {
                $this->$name = $value;
            }
        }
        $this->save();
    }    
}