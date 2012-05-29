<?php

namespace Needsandfun\Controller;

class Events extends \Core\Abstracts\Authorized
{
    public function __construct()
    {
        parent::__construct();

        if (!empty($_POST['addEvent']))
        {
            Cabinet::addEvent($_POST);
        }

        Index::filters('events');

        $this->page['clearFilter'] = $this->router->generate('events_index');
    }

    public function actions($eventId)
    {
        $event = \Core\Model\Event::find($eventId);

        if (!$event) return;

        switch($_POST['action'])
        {
            case 'confirm':
                $event->addMember($this->getClient());
                break;
            case 'cancel':
                $event->removeMember($this->getClient());
                break;
        }
        $result = array(
            'total' => count($event->members)
        );

        echo json_encode($result);
    }

	private function _categories($selected = false)
    {    
        $this->page['eventCategories'] = \Core\Model\Eventcategory::getAll($selected);
    }

	public function index()
    {
        $this->_categories();
        $this->page['categories'] = \Core\Model\Eventcategory::getAll();

        $filter = isset($_GET['filter'])
            ? $_GET['filter']
            : false;

        $sorter = isset($this->page['sort'])
            ? $this->page['sort']
            : array('type' => 'date', 'dir' => 'asc');

        $this->page['items']  = \Core\Model\Event::actual(false, $filter, $sorter);
        $this->page['metros'] = \Core\Model\Metro::getAll();

        $this->page->display('events/index.twig');
    }

    public function reporter($reporterId)
    {
        $client = \Core\Model\Client::find($reporterId);

        $this->page['item'] = $client;
        $this->page['pastEvents']   = \Core\Model\Event::clientsPast($client);
        $this->page['shopBanners']  = \Core\Model\Banner::shop(4);

        $this->page->display('events/reporter.twig');
    }

    public function sponsor($sponsor)
    {
        $sponsor = \Core\Model\Sponsor::from_url($sponsor);

        $this->page['item'] = $sponsor;
        $this->page['pastEvents']   = \Core\Model\Event::sponsorPast($sponsor);
        $this->page['comingEvents'] = \Core\Model\Event::sponsorComing($sponsor);
        $this->page['shopBanners']  = \Core\Model\Banner::shop(4);

        $this->page['wwws']   = explode(',', $this->page['item']->link);

        $this->page->display('events/sponsor.twig');
    }

    public function event($event)
    {
        $event = \Core\Model\Event::from_url($event);

        $this->page['item']       = $event;

        if ($this->getClient())
        {
            $this->page['isMembered'] = $this->getClient()->isMember($event);
        }

        $this->page['thisWeek'] = \Core\Model\Event::thisWeek();
        $this->page['shopBanners'] = \Core\Model\Banner::shop(4);

        $this->page->display('events/event.twig');
    }

    public function category($category, $page = 1) 
    {   
        $this->page['currentCategory'] = \Core\Model\Eventcategory::from_url($category); 
        $categoryId = $this->page['currentCategory']->id;

        $this->_categories($categoryId);
        $this->getStorage('flash')->setValue('eventCategoryId', $categoryId);
        
        $categories = $this->page['currentCategory']->getChildren();

        $filter = isset($_GET['filter'])
            ? $_GET['filter']
            : false;

        $sorter = isset($_GET['sort'])
            ? $_GET['sort']
            : array('type' => 'date', 'dir' => 'desc');

        $options = array(
            'categories' => $categories,
            'filter'     => $filter,
            'sort'       => $sorter
        );

        $this->page['items']      = \Core\Model\Event::getAll($options);
        $this->page['metros']     = \Core\Model\Metro::getAll();

        $this->page->display('events/index.twig');
    }

}