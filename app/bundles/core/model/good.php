<?php 

namespace Core\Model;

class Good extends \ActiveRecord\Model implements Itemswithpics, Reviewable, Searchable, Humanizeurl
{
    static $perPage = 25;

    static $age_ranges = array(
        0 => array(0, 3),
        3 => array(3, 5),
        5 => array(5, 7),
        7 => array(7, 9),
        9 => array(9, 12)
    );

    static $has_many = array(
        array('goodpictures', 'order' => 'weight', 'foreign_key' => 'item_id'),
        array('goodcategories'),
        array('goodproperties'),
        array('sizes'),     
        array('properties', 'through' => 'goodproperties'),
        array('categories', 'through' => 'goodcategory'),
        array('goodreviews', 'class' => 'Goodreview', 'foreign_key' => 'item_id'),
        array('reviews', 'foreign_key' => 'item_id', 'through' => 'goodreview', 'conditions' => 'is_checked = 1', 'order' => 'created desc')
    );
    
    static $belongs_to = array(
        array('type'),
        array('brand'),
        array('supplier')
    );

    static $before_save = array('updateLink');

    public function updateLink()
    {
      if($this->link == '') {
        $this->link = \Core\Url::encode($this->name);
      }
    }

    public function get_url()
    {
        $url = \Core\Router::get()->generate('shop_good', array('good' => $this->encoded_key));
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
        return 'good';
    }

    static function getAll()
    {
        $options = array(
            'conditions' => 'deleted is null and id not in (
                select good_id 
                from goods_categories 
                inner join categories on category_id=categories.id and categories.deleted is null
            )'
        );

        return self::all($options);
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
        return new Goodreview();
    }

    public function reviews()
    {
        return $this->reviews;
    }

    public function rating()
    {
        return $this->rating;
    }

    static function newGoods($limit = 4)
    {
        return self::all(array('conditions' => 'is_available = 1 and is_new = 1 and deleted is null', 'order' => 'created desc', 'limit' => $limit));
    }
    
    static function topGoods($limit = 4)
    {
        return self::all(array('conditions' => 'is_available = 1 and deleted is null', 'order' => 'sell_amount desc', 'limit' => $limit));
    }

    static function getPager($page, $category = false, $route, $total = 0)
    {
        $options = array();
        $routeParams['current'] = $page;

        if ($category)
        {
            $routeParams['category'] = $category;
            $options['category'] = $category;
        }

        $pager = array(
            'total'   => ceil(count($total)/self::$perPage),
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
        
    
    public function getBindId()
    {
        return $this->id;
    }
    
    /**
     * генерируем массив с иконками
     */
    public function getIcons()
    {
        return $this->goodpictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }
    
    public function getMaxPicWeight() 
    {
        $weight = Goodpicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }

    /**
     * показать товар
     */    
    public function show()
    {
        $this->_visible(true);
    }

    /**
     * спрятать товар
     */        
    public function hide()
    {
        $this->_visible(false);    
    }

    /**
     * показать/спрятать товар
     */
    private function _visible($isAvailable) 
    {
        $this->is_available = $isAvailable;
        $this->save();
    }
    
    /**
     * проверяем доступность
     */
    public function isAvailable()
    {
        return $this->is_available
            ? true
            : false;
    }
    
    /**
     * размеры, которые
     */
    public function get_sizes()
    {
        $sizes = \Core\Model\Size::all(array('conditions' => array('good_id=? and deleted IS NULL', $this->id)));
        return $sizes;
    }
    
    /**
     * цена. 
     * по-умолчанию, возвращается стоимость первого размера
     */
    public function get_price()
    {
        return count($this->sizes)
            ? $this->sizes[0]->getDiscountedPrice()
            : 0;
    }

    public function get_old_price()
    {
        return count($this->sizes)
            ? $this->sizes[0]->price
            : 0;
    }
    
    /**
     * категории товара через запятую
     * используется в админке
     */
    public function getCategories($returnArray = false)
    {
        $categories = array();
        
        foreach ($this->goodcategories as $_category)
        {
            if (@$_category->category->is_visible && @$_category->category->deleted == null)
            {
                array_push($categories, $_category->category_id);    
            }
        }

        if ($this->is_new)
        {
            array_push($categories, 'new');    
        }
        
        if (!$returnArray)
        {
            $categories = implode(', ', $categories);
        }
        
        return $categories;
    }
    
    public function getCategory()
    {
        $category = current($this->getCategories(true));

        return $category;
    }

    public function get_category()
    {
        $category = false;
        foreach ($this->goodcategories as $goodcategory) 
        {
            $category = $goodcategory->category;
            break;
        }

        return $category;
    }
    
    /**
     * забираем все допсвойства товара из его типа
     */
    public function getProperties() 
    {
        $properties = array();
        
        foreach ($this->type->properties as $property)
        {
            $item = array(
                'id'    => $property->id,
                'type'  => $property->propertytype->type,
                'name'  => $property->name,
                'defaultValue' => $property->getValues()
            );
            
            if ('array' == $item['type'])
            {
                $values = array();
                
                foreach ($property->propertyvalues as $value)
                {
                    $values[$value->id] = array(
                        'name' => $value->value
                    );
                }
                
                $item['value'] = $values;
            }
            else 
            {
                $item['value'] = false;
            }
            
            $properties[$property->id] = $item;
        }
        
        foreach ($this->goodproperties as $goodproperty) 
        {
            $property = &$properties[$goodproperty->property->id];
            
            if ('array' == $property['type'])
            {
                if (isset($property['value'][$goodproperty->value]))
                {
                    $property['value'][$goodproperty->value]['selected'] = true;
                }
            }
            else 
            {
                $property['value'] = $goodproperty->value;
            }
        }
        // print_r($properties);
        // die();
        
        return $properties;
    }
}