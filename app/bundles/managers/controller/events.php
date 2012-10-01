<?php

namespace Managers\Controller;

class Events extends \Core\Abstracts\Singleton
{
    protected function __construct()
    {
        if (isset($_POST['proceed']))
        {
            $this->_proceed($_POST);
        }
        else if (isset($_POST['proceed_category']))
        {
            $this->_proceedCategory($_POST);
        }        
        else 
        {
            Index::drawMenu();
        }
    }

    private function _proceedCategory($values)
    {
        $category = isset($values['id'])
            ? \Core\Model\Eventcategory::find($values['id'])
            : new \Core\Model\Eventcategory();
            
        $category->name       = $values['name'];
        $category->is_visible = $values['visible'];

        $category->description      = $values['description'];
        $category->meta_keywords    = $values['meta_keywords'];
        $category->meta_description = $values['meta_description'];

        $category->parent_id  = 'root' != $values['parent'] 
            ? $values['parent']
            : null;

        $category->save();
        
        $this->router->go($this->router->generate('manage_events_index'));
    }
///////////////////////////////////
    private function _getEvents($page = 1, $categories = false, $category = false)
    {
    
    
       
        $conditions = array(
            'events.deleted is null'
        );

        if ($categories)
        {
            $conditions[] = 'events.id in (select event_id from event_cats where category_id in (' . implode(', ', $categories) . '))';
        }

        $options = array('conditions' => array(implode(' AND ', $conditions)));
        
        if (!empty($variables))
        {
            $options['conditions'] = array_merge($options['conditions'], $variables);
        }

    //    $this->page['clearFilter'] = $this->router->generate($categories ? 'shop_category_page' : 'shop_index_page', array('page' => 'all', 'category' => $category));
        
        if (is_numeric($page))
        {
            $total = \Core\Model\Event::all($options);

            $options['limit']  = \Core\Model\Event::$perPage;
            $options['offset'] = \Core\Model\Event::$perPage * ($page - 1);

            $this->page['pager']   = \Core\Model\Event::getPager($page, $category, 'manage_events_category_page', $total);
            
          //  var_dump($this->page['pager']);
        }
        
        $options['order']  = 'name';

        $goods = \Core\Model\Event::all($options);

        if (isset($total) && count($total) > 0 && count($goods) == 0 && $page > 1)
        {
            $this->router->go($this->router->generate('category_page', array('page' => 1)));
        }

        return $goods;
    }

    private function _categories($selected = false)
    {    
        $this->page['eventCategories'] = \Core\Model\Eventcategory::getAll($selected);
    }

    public function category_index($category, $page = 1) 
    {
    
        if($category != 'new') {
          $this->page['currentCategory'] = \Core\Model\Eventcategory::from_url($category);
          $category = $this->page['currentCategory']->encoded_key;
  
          $categoryId = $this->page['currentCategory']->id;
  
          $filters = array('categoryId' => $categoryId);
          $this->getStorage('flash')->setValue('categoryId', $categoryId);
          $this->_categories($categoryId);
              
          $categories = $this->page['currentCategory']->getChildren();
        }
        
        $this->page['events'] = $this->_getEvents($page, $categories, $category);

        $this->page['categories'] = \Core\Model\Eventcategory::getAll();

        $this->page->display('events/index.twig');

    }
    
    
    public function index($page = 1, $categories = false, $category = false, $query = false)
    {
        $this->page['selectedCategory'] = $this->cookieStorage->getValue('events_selectedCategory') ?: 'all';

        $this->page['categories'] = \Core\Model\Eventcategory::getAll();

        $total = \Core\Model\Event::all();

        $options['limit']  = \Core\Model\Event::$perPage;
        $options['offset'] = \Core\Model\Event::$perPage * ($page - 1);
        
        $this->page['pager']   = \Core\Model\Event::getPager($page, $category, 'manage_events_index_page', $total);

        $this->page['events'] = \Core\Model\Event::all(array('conditions' => array('deleted is null'), 
          'limit' => $options['limit'],
          'offset' => $options['offset']
         ));
          
        $this->page->display('events/index.twig');
    }    
    
///////////////////////////////////
/*
    public function index()
    {
        $this->page['selectedCategory'] = $this->cookieStorage->getValue('events_selectedCategory') ?: 'all';

        $this->page['categories'] = \Core\Model\Eventcategory::getAll();
        $this->page['events'] = \Core\Model\Event::_getAll();
        
        $this->page->display('events/index.twig');
    }
*/
    public function addCategory()
    {
        $category = new \Core\Model\Eventcategory();
        $category->name = $_POST['categoryName'];
        $category->save();
        
        $this->router->go($this->router->generate('manage_events_index'));
    }

    public function removeCategory()
    {
        $categoryId = $_POST['deleteCategory'];
        $category =\Core\Model\Eventcategory::find($categoryId);
        $category->deleted = new \DateTime();
        $category->save();
    }

    public function categoryVisibility()
    {
        $category = \Core\Model\Eventcategory::find($_POST['categoryId']);
        $func = $_POST['visible'] 
            ? 'show'
            : 'hide';

        $category->$func();
    }

    public function categoryWeight()
    {
        $weight = count($_POST);
        foreach($_POST as $id => $value)
        {
            $category = \Core\Model\Eventcategory::find($id);
            
            $category->parent_id = isset($value['parentId']) 
                ? $value['parentId']
                : null;
                
            $category->weight = $weight - ($value['index'] + 1);
            $category->save();
        }
    }

    public function action()
    {
        // print_r($_POST);
        // die();
        switch ($_POST['action'])
        {
            case 'addCategory':
                $this->_eventAddCategory($_POST['goodId'], $_POST['categoryId']);
                break;
            case 'show':
                $this->_showEvent($_POST['goodId']);
                break;
            case 'hide':
                $this->_hideEvent($_POST['goodId']);
                break;                
            case 'deleteFromCategory':
                $this->_eventDeleteCategory($_POST['goodId']);
                break;
            case 'deleteGood':
                $this->_eventDelete($_POST['goodId']);
                break;
        }
    }

    private function _eventDelete($itemId)
    {
        $item = \Core\Model\Event::find($itemId);
        $item->deleted = new \DateTime();
        $item->save();
    }    

    private function _eventAddCategory($item, $categoryId)
    {
        $event = \Core\Model\Event::find($item);
        $event->category_id = $categoryId;
        $event->save();
    }

    private function _eventDeleteCategory($item)
    {
        $event = \Core\Model\Event::find($item);
        $event->category_id = null;
        $event->save();
    }    

    private function _showEvent($itemId)
    {
        \Core\Model\Event::find($itemId)->show();
    }
    
    private function _hideEvent($itemId)
    {
        \Core\Model\Event::find($itemId)->hide();
    }
    
    public function _proceed($values)
    {        
        $event = empty($values['id'])
            ? new \Core\Model\Event()
            : \Core\Model\Event::find($values['id']);

        $event->name = $values['name'];
        
        if (!empty($values['age_from']) && 'any' != $values['age_from'])
        {
            $event->age_from = $values['age_from'];
        }
        else { $event->age_from = null; }
        
        if (!empty($values['age_to']) && 'any' != $values['age_to'])
        {
            $event->age_to   = $values['age_to'];
        }
        else { $event->age_to = null; }
        
        if (empty($values['id'])) 
        {
            $event->creator_id = 1;
        }
        
        $event->held_date    = new \DateTime($values['held_date']);
        $event->description  = $values['description'];
        $event->price        = $values['price'] ?: null;
        $event->category_id  = $values['category'] ?: null;
        $event->is_checked   = $values['moderation'];

        $event->meta_keywords    = $values['meta_keywords'];
        $event->title            = $values['title'];
        $event->meta_description = $values['meta_description'];

        if (!empty($values['place']))
        {
            $event->place_id     = $values['place'];    
        }

        $reporter = \Core\Model\Client::find($values['reporter']);
        if ($reporter) $event->setCreator($reporter);

        $event->save();
        
        foreach ($event->eventsponsors as $eventSponsor)
        {
            $eventSponsor->delete();    
        }
        
        if (!empty($values['sponsors'])) 
        {
            foreach ($values['sponsors'] as $sponsor)
            {
                $eventSponsor = new \Core\Model\Eventsponsor();
                $eventSponsor->event_id   = $event->id;
                $eventSponsor->sponsor_id = $sponsor;
                $eventSponsor->save();
            }
            
        }
        
        ////////////////
        foreach ($event->eventcats as $eventCat)
        {
            $eventCat->delete();    
        }
        
        if (!empty($values['category1'])) 
        {
            foreach ($values['category1'] as $category)
            {
                $eventCat = new \Core\Model\Eventcat();
                $eventCat->event_id   = $event->id;
                $eventCat->category_id = $category;
                $eventCat->save();
            }
            
        }
        //////////////////

        $itempicture = new \Core\Model\Eventpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $event, $itempicture);
        
        $this->router->go($this->router->generate('manage_events_index'));
    }
    
    public function add()
    {
        $this->page['categories'] = \Core\Model\Eventcategory::getAll();
        $this->page['placeCategories'] = \Core\Model\Placecategory::getAll();
        $categories = array();
        foreach ($this->page['event']->eventcats as $eventCat)
        {
            array_push($categories, $eventCat->category_id);   
        }
        $this->page['current_category'] = $categories;
        $this->_form();
    }    

    public function edit($eventId)
    {
        $this->page['event']    = \Core\Model\Event::find($eventId);
        
        $this->page['reporters']  = \Core\Model\Client::getAll();
        $this->page['sponsors']   = \Core\Model\Sponsor::getAll();
        $this->page['categories'] = \Core\Model\Eventcategory::getAll();
        $this->page['placeCategories'] = \Core\Model\Placecategory::getAll();
        
        $categories = array();
        foreach ($this->page['event']->eventcats as $eventCat)
        {
            array_push($categories, $eventCat->category_id);   
        }
        $this->page['current_category'] = $categories;
        
        $this->_form();
    }   

    public function remove($eventId)
    {
        $event = \Core\Model\Event::find($eventId);
        $event->deleted = new \DateTime();
        $event->save();
        
        $this->router->go($this->router->generate('manage_events_index'));
    }    
    
    public function _form()
    {
        $page = $this->getPage();

        $page->display('events/form.twig');        
    }

    public function editCategory($categoryId)
    {
        $this->page['item'] = \Core\Model\Eventcategory::find($categoryId);
        $this->page['categories'] = \Core\Model\Eventcategory::all(array('conditions' => array('deleted is null and parent_id is null and NOT(id = ?)', $categoryId), 'order' => 'name'));
        $this->page->display('events/category.twig');
    }
}
