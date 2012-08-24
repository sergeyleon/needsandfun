<?php

namespace Managers\Controller;

class Index extends \Core\Abstracts\Singleton
{
    public function index()
    {
        $page = $this->getPage();      
        Index::drawMenu();

        $page->display('index.twig');
    }

    public function updateAllLinks()
    {
        $result = array();

        foreach (\Core\Model\Good::all() as $item)
        {
            if (!isset($result['Товары'])) $result['Товары'] = array();
            $item->updateLink();
            $item->save();
            $result['Товары'][] = array('name' => $item->name, 'link' => $item->link);
        }

        foreach (\Core\Model\Category::all() as $item)
        {
            if (!isset($result['Категории'])) $result['Категории'] = array();
            $item->updateLink();
            $item->save();
            $result['Категории'][] = array('name' => $item->name, 'link' => $item->link);
        }

        foreach (\Core\Model\Article::all() as $item)
        {
            if (!isset($result['Статьи'])) $result['Статьи'] = array();
            $item->updateLink();
            $item->save();
            $result['Статьи'][] = array('name' => $item->name, 'link' => $item->link);
        }
        
        foreach (\Core\Model\News::all() as $item)
        {
            if (!isset($result['Новости'])) $result['Новости'] = array();
            $item->updateLink();
            $item->save();
            $result['Новости'][] = array('name' => $item->name, 'link' => $item->link);
        }

        foreach (\Core\Model\Client::all() as $item)
        {
            if (!isset($result['Клиенты'])) $result['Клиенты'] = array();
            $item->updateLink();
            $item->save();
            $result['Клиенты'][] = array('name' => $item->name, 'link' => $item->link);
        }

        foreach (\Core\Model\Event::all() as $item)
        {
            if (!isset($result['События'])) $result['События'] = array();
            $item->updateLink();
            $item->save();
            $result['События'][] = array('name' => $item->name, 'link' => $item->link);
        }

        foreach (\Core\Model\Eventcategory::all() as $item)
        {
            if (!isset($result['События:категории'])) $result['События:категории'] = array();
            $item->updateLink();
            $item->save();
            $result['События:категории'][] = array('name' => $item->name, 'link' => $item->link);
        }

        foreach (\Core\Model\Place::all() as $item)
        {
            if (!isset($result['Места'])) $result['Места'] = array();
            $item->updateLink();
            $item->save();
            $result['Места'][] = array('name' => $item->name, 'link' => $item->link);
        }

        foreach (\Core\Model\Sponsor::all() as $item)
        {
            if (!isset($result['Организаторы'])) $result['Организаторы'] = array();
            $item->updateLink();
            $item->save();
            $result['Организаторы'][] = array('name' => $item->name, 'link' => $item->link);
        }

        foreach (\Core\Model\Placecategory::all() as $item)
        {
            if (!isset($result['Места:категории'])) $result['Места:категории'] = array();
            $item->updateLink();
            $item->save();
            $result['Места:категории'][] = array('name' => $item->name, 'link' => $item->link);
        }

        $this->page['results'] = $result;

        Index::drawMenu();
        $this->page->display('update_all_links.twig');
    }
    
    public function processPictures()
    {
        $type = $_POST['type'];
        
        if ('good' == $type)
        {
            $class = '\Core\Model\Goodpicture';
        }
        else if ('brand' == $type)
        {
            $class = '\Core\Model\Brandpicture';            
        }        
        else if ('place' == $type)
        {
            $class = '\Core\Model\Placepicture';
        }
        else if ('partner' == $type)
        {
            $class = '\Core\Model\Partnerpicture';
        }
        else if ('event' == $type)
        {
            $class = '\Core\Model\Eventpicture';
        }                
        else if ('sponsor' == $type)
        {
            $class = '\Core\Model\Sponsorpicture';
        }
        else if ('banner' == $type)
        {
            $class = '\Core\Model\Bannerpicture';
        }
        else if ('client' == $type)
        {
            $class = '\Core\Model\Clientpicture';
        }
        else if ('article' == $type)
        {
            $class = '\Core\Model\Articlepicture';
        }
        else if ('news' == $type)
        {
            $class = '\Core\Model\Newspicture';
        }
        else if ('author' == $type)
        {
            $class = '\Core\Model\Authorpicture';
        }


        if (isset($_POST['deletePicture']))
        {
            $class::find($_POST['deletePicture'])->delete();        
        }
        else 
        {
            $ids = explode(',', $_POST['ids']);
            $class::updateWeights($ids);            
        }
    }    

    /**
     * генерируем админское меню
     * необходимо вставлять в конструкторы контроллеров
     */    
    static public function drawMenu()
    {
        $page = \Core\Page::get();

        $page['manageMenu'] = array(
            array(
                'name'  => 'Главная',
                'route' => 'manage_index'
            ),

            array(
                'name'  => 'События',
                'route' => 'manage_events_index',
                'children' => array(
                    array(
                        'name'  => 'Места',
                        'route' => 'manage_places_index'
                    ),
                    array(
                        'name'  => 'Организаторы',
                        'route' => 'manage_sponsors_index'
                    ),                    
                    array(
                        'name'  => 'Метро',
                        'route' => 'manage_metro_index'
                    )
                )
            ),

            array(
                'name'  => 'Магазин',
                'route' => 'manage_goods_index',
                'children' => array(
                    array(
                        'name'  => 'Отзывы',
                        'route' => 'manage_reviews_index',
                    ),
                    array(
                        'name'  => 'Типы товаров',
                        'route' => 'manage_goods_types'
                    ),
                    array(
                        'name'  => 'Бренды',
                        'route' => 'manage_brands_index'
                    ),
                    array(
                        'name'  => 'Партнеры',
                        'route' => 'manage_partners_index'
                    ),
                    array(
                        'name'  => 'Поставщики',
                        'route' => 'manage_suppliers_index'
                    ),
                    array(
                        'name'  => 'Работа с массивами',
                        'route' => 'manage_goods_exp'
                    )
                )
            ),

            array(
                'name'  => 'Заказы',
                'route' => 'manage_orders_index',
                'children' => array(
                    array(
                        'name'  => 'Клиенты',
                        'route' => 'manage_clients_index'
                    ),
                    array(
                        'name'  => 'Доставки',
                        'route' => 'manage_deliveries_index'
                    ),
                    array(
                        'name'  => 'Обращения',
                        'route' => 'manage_callbacks_index'
                    )
                )
            ),

            array(
                'name'  => 'Статьи',
                'route' => 'manage_articles_index',
                'children' => array(
                    array(
                        'name'  => 'Авторы',
                        'route' => 'manage_articles_authors_index'
                    )
                )
            ),
            array(
                'name'  => 'Новости',
                'route' => 'manage_news_index'
                
            ),
            array(
                'name'  => 'Настройки',
                'children' => array(
                    array(
                        'name'  => 'Баннеры',
                        'route' => 'manage_banners_index'
                    ),
                    array(
                        'name'  => 'Страницы',
                        'route' => 'manage_pages'
                    ),
                    array(
                        'name'  => 'Обновить все ссылки',
                        'route' => 'manage_update_all_links'
                    )
                )
            )        
        );
    }
}