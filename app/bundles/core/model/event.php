<?php 

namespace Core\Model;

class Event extends \ActiveRecord\Model implements Itemswithpics, Reviewable, Searchable, Humanizeurl
{
    static $perPage = 24;

    static $age_ranges = array(
        0 => array(0, 3),
        3 => array(3, 5),
        5 => array(5, 7),
        7 => array(7, 9),
        9 => array(9, 12)
    );

    static $belongs_to = array(
        array('place'),
        array('category', 'class' => 'Eventcategory', 'foreign_key' => 'category_id'),
        array('creator', 'class' => 'Client', 'foreign_key' => 'creator_id')
    );
    
    static $has_many = array(
        array('members'),    
        array('eventsponsors'),
        array('eventpictures', 'order' => 'weight', 'foreign_key' => 'item_id'),
        array('eventreviews', 'class' => 'Eventreview', 'foreign_key' => 'item_id'),
        array('reviews', 'foreign_key' => 'item_id', 'through' => 'eventreview', 'conditions' => 'is_checked = 1', 'order' => 'created desc')
    );

    static $before_save = array('updateLink');

    public function updateLink()
    {
        $key = $this->held_date
            ? $this->held_date->format('dmy')
            : $this->id;

        $this->link = \Core\Url::encode($this->name . ' ' . $key);
    }

    public function get_url()
    {
        $url = \Core\Router::get()->generate('events_event', array('event' => $this->encoded_key));
        return $url;
    }

    public function get_encoded_key()
    {
        return $this->link;
    }

    static function from_url($url)
    {
        $result = self::find_by_link($url);
        return $result;
    }

    public function getType()
    {
        return 'event';
    }

    public function updateRating()
    {
        $total = 0;
        $count = 0;

        foreach ($this->reviews as $review)
        {
            $total += $review->rating;
            $count++;
        }

        $this->rating = round($total/$count);
        $this->save();
    }

    public function getLinkModel()
    {
        return new Eventreview();
    }

    public function reviews()
    {
        return $this->reviews;
    }

    public function rating()
    {
        return $this->rating;
    }


    public function isMembered(Client $client)
    {
        return $client->isMember($this);
    }

    public function addMember (Client $client)
    {
        $member = new Member();
        $member->event_id  = $this->id;
        $member->client_id = $client->id;
        $member->save();
    }

    public function removeMember (Client $client) 
    {
        $members = Member::getAll(array(
            'client' => $client->id,
            'event'  => $this->id
        ));

        foreach ($members as $member)
        {
            $member->delete();
        }
    }

    static function add($values)
    {
        $client = \Core\Abstracts\Authorized::get()->getClient();

        if (!$client) 
        {
            return false;
        }

        $event = new self();

        if (!empty($values['age_from']) && 'any' != $values['age_from'])
        {
            $event->age_from = $values['age_from'];
        }

        if (!empty($values['age_to']) and 'any' != $values['age_to'])
        {
            $event->age_to = $values['age_to'];
        }

        if (!empty($values['title']))
        {
            $event->name = $values['title'];
        }

        if (!empty($values['description']))
        {
            $event->description = $values['description'];
        }

        if (!empty($values['category']))
        {
            $event->category_id = $values['category'];
        }

        if (!empty($values['place']))
        {
            $event->place_hint = $values['place'];
        }

        if (!empty($values['sponsor']))
        {
            $event->sponsor_hint = $values['sponsor'];
        }

        if (!empty($values['price']))
        {
            $event->price = $values['price'];
        }

        if (!empty($values['date']))
        {
            $time = empty($values['time'])
                ? '12:00'
                : $values['time'];

            $event->held_date = new \DateTime($values['date'] . $time);
        }

        $event->setCreator($client);

        $itempicture = new \Core\Model\Eventpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $event, $itempicture);

        return $event;
    }

    public function setCreator(Client $client = null)
    {
        $this->creator_id = $client->id;
        $this->save();
    }

    public function get_sponsors()
    {
        $selected = array();
        
        foreach ($this->eventsponsors as $eventSponsor)
        {
            array_push($selected, $eventSponsor->sponsor_id);
        }

        $sponsors = array();
        foreach (Sponsor::getAll() as $sponsor)
        {
            $sponsorName = $sponsor->name;
            $sponsorId   = $sponsor->id;

            $sponsor = $sponsor->to_array();
            $sponsor['name'] = $sponsorName;
            $sponsor['id']   = $sponsorId;

            if (in_array($sponsorId, $selected))
            {
                $sponsor['selected'] = true;
            }

            array_push($sponsors, $sponsor);
        }

        return $sponsors;
    }
    
    public function getBindId()
    {
        return $this->id;
    }    

    public function getIcons()
    {
        return $this->eventpictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }
    
    public function getMaxPicWeight() 
    {
        $weight = Eventpicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }    

    public function isAvailable()
    {
        return $this->is_checked;
    }

    public function show()
    {
        $this->is_checked = 1;
        $this->save();
    }

    public function hide()
    {
        $this->is_checked = 0;
        $this->save();
    }    

    public function get_ages()
    {
        $ages = null;
        
        if ($this->age_from && $this->age_to)
        {
            $ages = $this->age_from . '—' . $this->age_to;
        }
        else if ($this->age_from)
        {
            $ages = 'с ' . $this->age_from;
        }
        else if ($this->age_to)
        {
            $ages = 'до ' . $this->age_to;
        }

        return $ages;
    }

    public function getCategory()
    {
        return $this->category_id;
    }

    public function isFresh()
    {
        $now = new \DateTime();

        return $now < $this->held_date;
    }

    static function getAll($data)
    {
        $conditions = array(
            'events.is_checked = 1',
            'events.deleted is null'
        );

        $variables = array();

        // $categories = false, $filter = false, $sorter = false
        if (isset($data['categories']))
        {
            $conditions[] = 'events.category_id in (?)';
            $variables[]  = $data['categories'];
        }
        else {
            $conditions[] = 'events.category_id in (select id from event_categories where is_visible=1)';
        }

        if (isset($data['from']))
        {
            $conditions[] = 'events.held_date >= ?';
            $variables[]  = $data['from'];
        }

        if (isset($data['filter']))
        {
            $filter = $data['filter'];

            if (isset($filter['age']))
            {
                $age  = $filter['age'];
                $ages = \Core\Model\Event::$age_ranges;

                if (isset($ages[$age]))
                {
                    $conditions[] = '((events.age_to is not null and events.age_to >= ?) or events.age_to is null)';
                    $conditions[] = '((events.age_from is not null and events.age_from < ?) or events.age_from is null)';

                    $variables = array_merge($variables, $ages[$age]);
                }
            }

            if (isset($filter['price']))
            {
                $price = $filter['price'];

                switch($price)
                {
                    case 'free':
                        $conditions[] = 'events.price is NULL';
                        break;

                    case 'pay':
                        $conditions[] = 'events.price is not NULL';
                        break;
                }
            }

            if (isset($filter['metros']))
            {
                $joins = 'join places on events.place_id = places.id';

                $conditions[] = 'places.metro_id in (?)';
                $variables[] = $filter['metros'];
            }
        }

        $options = array('conditions' => array(implode(' AND ', $conditions)));

        if (isset($joins))
        {
            $options['joins'] = $joins;
        }

        $sort = array();

        if (isset($data['sort']))
        {
            $sorter = $data['sort'];

            $dir = in_array($sorter['dir'], array('asc', 'desc'))
                ? $sorter['dir']
                : 'asc';

            switch ($sorter['type'])
            {
                case 'rating':
                    $sort[] = 'rating ' . $dir;
                    break;

                case 'date':
                    $sort[] = 'held_date ' . $dir;
                    break;

                case 'price':
                    $sort[] = 'price ' . $dir;
                    break;                    

                case 'abc':
                    $sort[] = 'name ' . $dir;
                    break;

                default: 
                    new \Exception('Alarm! Intruder!');
            }

            if ('abc' != $sorter['type'])
            {
                $sort[] = 'name asc';
            }
        }

        $options['order'] = count($sort)
            ? implode(', ', $sort)
            : 'name asc';    

        if (isset($data['limit']))
        {
            $options['limit'] = $data['limit'];
        }

        if (!empty($data['page']) && is_numeric($data['page']))
        {
            $options['limit']  = self::$perPage;
            $options['offset'] = self::$perPage * ($data['page'] - 1);
        }

        if (!empty($variables))
        {
            $options['conditions'] = array_merge($options['conditions'], $variables);
        }

        return self::all($options);
    }

    static function actual($limit = false, $filter = false, $sorter = false)
    {
        $options = array('from' => new \DateTime());

        if ($limit)
        {
            $options['limit'] = $limit;
        }

        if ($filter)
        {
            $options['filter'] = $filter;
        }

        if ($sorter)
        {
            $options['sort'] = $sorter;
        }

        return self::getAll($options);
    }

    static function coming(Place $place, $limit = 3)
    {
        $options = array(
            'conditions' => array(
                'date(held_date) >= date(?)
                and deleted is null
                and place_id = ?', 
                new \DateTime(),
                $place->id
            ),
            'limit' => $limit,
            'order' => 'held_date'
        );

        return self::all($options);
    }

    static function thisWeek($limit = 3)
    {
        $options = array(
            'conditions' => array(
                'date(held_date) >= date(?)
                and deleted is null', 
                new \DateTime()
            ),
            'limit' => $limit,
            'order' => 'held_date'
        );

        return self::all($options);
    }

    static function past(Place $place, $limit = 4)
    {
        $options = array(
            'conditions' => array(
                'held_date < ?
                and deleted is null
                and place_id = ?', 
                new \DateTime(),
                $place->id
            ),
            'limit' => $limit,
            'order' => 'held_date desc'
        );

        return self::all($options);
    }

    static function currentEvents(Client $client)
    {
        $options = array(
            'conditions' => array(
                'held_date > ?
                and deleted is null
                and id in (select event_id from members where client_id in (?))', 
                new \DateTime(),
                $client->id
            ),
            'order' => 'held_date'
        );

        return self::all($options);
    }    

    static function clientsPast(Client $client, $limit = 3)
    {
        $options = array(
            'conditions' => array(
                'held_date < ?
                and deleted is null
                and creator_id = ?', 
                new \DateTime(),
                $client->id
            ),
            'limit' => $limit,
            'order' => 'held_date desc'
        );

        return self::all($options);
    }

    static function sponsorPast(Sponsor $sponsor, $limit = 4)
    {
        $options = array(
            'conditions' => array(
                'held_date < ?
                and deleted is null
                and id in (select event_id from event_sponsors where sponsor_id=?)', 
                new \DateTime(),
                $sponsor->id
            ),
            'limit' => $limit,
            'order' => 'held_date desc'
        );

        return self::all($options);
    }    

    static function sponsorComing(Sponsor $sponsor, $limit = 3)
    {
        $options = array(
            'conditions' => array(
                'date(held_date) >= date(?)
                and deleted is null
                and id in (select event_id from event_sponsors where sponsor_id=?)', 
                new \DateTime(),
                $sponsor->id
            ),
            'limit' => $limit,
            'order' => 'held_date desc'
        );

        return self::all($options);
    }        
    
    static function _getAll($placeId = false)
    {
        $now = new \DateTime();
        
        $options = array(
            'conditions' => array('deleted is null'),
            'order'      => 'held_date, name'
        );

        //and date(held_date) >= ?', $now->format('Y-m-d')                          # для того, чтобы обрезать прошедшие события
    
        if ($placeId)
        {
            $options['conditions'][0] = $options['conditions'][0] . ' and place_id = ?';
            $options['conditions'][] = $placeId;
        }
        else {
            $options['conditions'][0] = $options['conditions'][0] . ' and (place_id in (select id from places where deleted is null) or place_id is NULL)';
        }

        return self::all($options);
    }    
    
    static function getCurrent($placeId = false)
    {
        $now = new \DateTime();
        
        $options = array(
            'conditions' => array('deleted is null and is_checked=1'),
            'order'      => 'name'
        );
        // and date(held_date) > date(?)', $now->format('Y-m-d')                    # для того, чтобы обрезать прошедшие события
    
        if ($placeId)
        {
            $options['conditions'][0] = $options['conditions'][0] . ' and place_id = ?';
            $options['conditions'][] = $placeId;
        }
        else {
            $options['conditions'][0] = $options['conditions'][0] . ' and place_id in (select id from place_categories where deleted is null)';        
        }
        
        return self::all($options);
    }    

    static function getPager($page, $category = false, $route, $filter = false, $sorter = false)
    {
        $options = array();
        $routeParams['current'] = $page;

        if ($category)
        {
            $options['categories'] = $category->getChildren();
            $routeParams['category'] = $category->encoded_key;
        }

        if ($filter)
        {
            $options['filter'] = $filter;
        }

        if ($sorter)
        {
            $options['sorter'] = $sorter;
        }

        $events = self::getAll($options);

        $pager = array(
            'total'   => ceil(count($events)/self::$perPage),
            'current' => $page,
            'route'   => $route,
            'routeParams' => $routeParams
        );

        if ($pager['total'] == 1)
        {
            $pager = false;
        }

        return $pager;
    }  
}
