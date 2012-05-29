<?php

namespace Core\Model;

class Sponsor extends \ActiveRecord\Model implements Itemswithpics, Humanizeurl
{
    static $has_many = array(
        'eventsponsors',
        array('sponsorpictures', 'foreign_key' => 'item_id', 'order' => 'weight')
    );

    static $before_save = array('updateLink');

    public function updateLink()
    {
        $this->link = \Core\Url::encode($this->name);
    }

    public function get_url()
    {
        $url = \Core\Router::get()->generate('events_sponsor', array('sponsor' => $this->encoded_key));
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

    static function getAll()
    {
        $options = array(
            'conditions' => 'deleted is null',
            'order' => 'name'
        );

        return self::all($options);
    }

    public function getBindId()
    {
        return $this->id;
    }    

    public function getIcons()
    {
        return $this->sponsorpictures;
    }
    
    public function getIcon()
    {
        return current($this->getIcons());
    }

    public function getMaxPicWeight() 
    {
        $weight = Sponsorpicture::first(array(
            'conditions' => array('item_id = ?', $this->id),
            'order'      => 'weight desc',
            'limit'      => 1
        ));
        
        return $weight
            ? ($weight->weight + 1)
            : 0;
    }    
        
}