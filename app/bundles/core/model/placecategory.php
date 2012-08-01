<?php 

namespace Core\Model;

class Placecategory extends \ActiveRecord\Model implements Humanizeurl
{
    static $table = 'place_categories';
    static $has_many = array(
        array('places', 'foreign_key' => 'category_id' , 'conditions' => 'deleted is null', 'order' => 'name')
    );

    public $current = false;

    static $before_save = array('updateLink');

    public function updateLink()
    {
        $this->link = \Core\Url::encode($this->name);
    }

   public function __toString() {
    return "";
   }

    public function get_url()
    {
        $url = \Core\Router::get()->generate('places_category', array('category' => $this->encoded_key));
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

    static function getAll($selected = false)
    {
        $options = array(
            'conditions' => 'deleted is null',
            'order'      => 'name'
        );
        
        $result = self::all($options);
        
        foreach ($result as &$row)
        {    
            if ($selected == $row->id)        
            {
                $row->current = true;
            }
        }
        
        return $result;
    } 
    
    public function getPlaces()
    {
        $places = Place::getAll(array('category' => $this->id));
        return $places;
    }
}
