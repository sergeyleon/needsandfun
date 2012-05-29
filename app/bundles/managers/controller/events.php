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

    public function index()
    {
        $this->page['selectedCategory'] = $this->cookieStorage->getValue('events_selectedCategory') ?: 'all';

        $this->page['categories'] = \Core\Model\Eventcategory::getAll();
        $this->page['events'] = \Core\Model\Event::_getAll();
        
        $this->page->display('events/index.twig');
    }

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
        
        if (!empty($values['age_to']) && 'any' != $values['age_to'])
        {
            $event->age_to   = $values['age_to'];
        }
        
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

        $itempicture = new \Core\Model\Eventpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $event, $itempicture);
        
        $this->router->go($this->router->generate('manage_events_index'));
    }
    
    public function add()
    {
        $this->_form();
    }    

    public function edit($eventId)
    {
        $this->page['event']    = \Core\Model\Event::find($eventId);
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
        $this->page['reporters']  = \Core\Model\Client::getAll();
        $this->page['sponsors']   = \Core\Model\Sponsor::getAll();
        $this->page['categories'] = \Core\Model\Eventcategory::getAll();
        $this->page['placeCategories'] = \Core\Model\Placecategory::getAll();

        $this->page->display('events/form.twig');        
    }

    public function editCategory($categoryId)
    {
        $this->page['item'] = \Core\Model\Eventcategory::find($categoryId);
        $this->page['categories'] = \Core\Model\Eventcategory::all(array('conditions' => array('deleted is null and parent_id is null and NOT(id = ?)', $categoryId), 'order' => 'name'));
        $this->page->display('events/category.twig');
    }
}
