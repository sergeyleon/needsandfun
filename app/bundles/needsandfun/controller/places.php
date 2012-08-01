<?php

namespace Needsandfun\Controller;

class Places extends \Core\Abstracts\Authorized
{
    public function __construct()
    {
        parent::__construct();
        Index::filters('places');
    }

    public function index($page = false) 
    {
        $this->category(false, $page ?: 1);
    }
    
    public function place($place)
    {
        $place = \Core\Model\Place::from_url($place);
        $this->page['item'] = $place;
        
        $this->page['emails'] = explode(',', $this->page['item']->email);
        $this->page['wwws']   = explode(',', $this->page['item']->www);

        $this->page['comingEvents'] = \Core\Model\Event::coming($place);
        $this->page['pastEvents']   = \Core\Model\Event::past($place);
        $this->page['shopBanners']  = \Core\Model\Banner::shop(4);
        
        $this->page->display('places/place.twig');
    }

    public function category($category = false, $page = 1)
    {
        $categoryKey = $category;
        if ($category)
        {
            $category = \Core\Model\Placecategory::from_url($category);
            $categoryId = $category->id;    
        }
        else {
            $categoryId = false;
        }
        

        $route = $categoryId 
            ? 'places_category_page'
            : 'places_index_page';
        
        $this->page['currentCategory'] = $categoryId;
        $filters = array('categoryId' => $categoryId);

        $filter = isset($_GET['filter'])
            ? $_GET['filter']
            : false;

        $sorter = isset($this->page['sort'])
            ? $this->page['sort']
            : array('type' => 'rating', 'dir' => 'desc');

        $this->page['clearFilter'] = $this->router->generate($route, array('category' => $category, 'page' => 'all'));

        $this->page['items']           = \Core\Model\Place::getPage($page, $categoryId, $filter, $sorter);

        if (is_numeric($page))
        {
            $this->page['pager'] = \Core\Model\Place::getPager($page, $category, $route, $filter);
        }
        

        $this->page['categories']      = \Core\Model\Placecategory::getAll($categoryId);
        $this->page['metros']          = \Core\Model\Metro::getAll();
        
        $this->page->display('places.twig');
    }    
}