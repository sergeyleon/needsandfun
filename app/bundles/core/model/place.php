<?php 

namespace Core\Model;

class Place extends \ActiveRecord\Model implements Itemswithpics, Reviewable, Searchable, Humanizeurl
{
    static $perPage = 24;

    static $belongs_to = array(
        array('metro'),
        array('category', 'class' => 'Placecategory', 'foreign_key' => 'category_id')
    );
    
    static $has_many = array(
        array('placepictures', 'order' => 'weight', 'foreign_key' => 'item_id'),
        array('placereviews', 'class' => 'Placereview', 'foreign_key' => 'item_id'),
        array('reviews', 'foreign_key' => 'item_id', 'through' => 'placereview', 'conditions' => 'is_checked = 1', 'order' => 'created desc')
    );

    static $before_save = array('updateLink');

    public function updateLink()
    {
        $this->link = \Core\Url::encode($this->name);
    }

    public function get_url()
    {
        $url = \Core\Router::get()->generate('places_place', array('place' => $this->encoded_key));
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
        return 'place';
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
        return new Placereview();
    }

    public function reviews()
    {
        return $this->reviews;
    }

    public function rating()
    {
        return $this->rating;
    }
    
    public function getBindId()
    {
        return $this->id;
    }    

    public function getIcons()
    {
        return $this->placepictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }
    
    public function getMaxPicWeight() 
    {
        $weight = Placepicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }    

    static function banners()
    {
        $result = self::all(array(
            'conditions' => 'is_checked=1 and deleted is null',
            'limit'      => '5',
            'order'      => 'created desc'
        ));

        return $result;
    }

    static function getAll($_options = false)
    {
        $options = array(
            'conditions' => array('deleted is null and is_checked=1'),
            'order'      => 'name'
        );

        if (!empty($_options['category']))
        {
            $options['conditions'][0] = $options['conditions'][0] . ' and category_id = ?';
            $options['conditions'][1] = $_options['category'];
        }
        else {
            $options['conditions'][0] = $options['conditions'][0] . ' and category_id in 
                (select id from place_categories where deleted is null)';
        }

        if (!empty($_options['page']))
        {
            $options['limit']  = self::$perPage;
            $options['offset'] = self::$perPage * ($_options['page'] - 1);
        }

        if (!empty($_options['filter']))
        {
            $filter = $_options['filter'];

            if (isset($filter['metros']))
            {
                $options['conditions'][0] .= ' and metro_id in (?)';
                $options['conditions'][] = $filter['metros'];
            }
        }

        $sort = array();

        if (!empty($_options['sorter']))
        {
            $sorter = $_options['sorter'];

            $dir = in_array($sorter['dir'], array('asc', 'desc'))
                ? $sorter['dir']
                : 'asc';

            switch ($sorter['type'])
            {
                case 'rating':
                    $sort[] = 'rating ' . $dir;
                    break;

                case 'date':
                    $sort[] = 'created ' . $dir;
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

        return self::all($options);
    }   

    static function getPage($page, $category, $filter = false, $sorter = false)
    {
        $options = array(
            'category' => $category,
            'page'     => $page
        );

        if ($filter)
        {
            $options['filter'] = $filter;
        }

        if ($sorter)
        {
            $options['sorter'] = $sorter;
        }

        return self::getAll($options);
    }    

    static function getPager($page, $category = false, $route, $filter = false, $sorter = false)
    {
        $options = array();
        $routeParams['current'] = $page;

        if ($category)
        {
            $options['category'] = $category->id;
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

        $places = self::getAll($options);

        $pager = array(
            'total'   => ceil(count($places)/self::$perPage),
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
