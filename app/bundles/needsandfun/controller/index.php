<?php

namespace Needsandfun\Controller;

class Index extends \Core\Abstracts\Authorized
{
    public function __construct()
    {
        parent::__construct();
        
        if (isset($_POST['authorize']))
        {
            $this->_auth($_POST['login'], $_POST['password']);
        }
        else if (isset($_POST['flush_password']))
        {
            $this->_flushPassword($_POST);
        }
    }

    private function _auth($login, $password)
    {
        \Core\Model\User::auth($login, $password);

        $url = empty($_SERVER['HTTP_REFERER'])
            ? $this->router->generate('index')
            : $_SERVER['HTTP_REFERER'];

        $this->router->go($url);
    }

    public function show()
    {
        $this->page['bigBanners']    = \Core\Model\Banner::big();
        $this->page['todayBanner']   = \Core\Model\Banner::todayInShop();
        $this->page['shopBanners']   = \Core\Model\Banner::shop();

        $this->page['partners']      = \Core\Model\Partner::banners();
        $this->page['places']        = \Core\Model\Place::banners();
        $this->page['articleBanner'] = current(\Core\Model\Article::banners());

        $this->page['actualEvents']  = \Core\Model\Event::actual(6);
        $this->page['categories'] = \Core\Model\Eventcategory::getAll();

        $this->page->display('index.twig');
    }

    static function filters($namespace = 'shop')
    {
        $storage = \Core\Storage::init('flash');
        $page    = \Core\Page::get(); 
        $router  = \Core\Router::get();

        if (isset($_GET['filter']))
        {
            $filter = $_GET['filter'];
            $storage->setValue($namespace . 'filter', $filter);            
        }
        else
        {
            $filter = false;
        }

        if (isset($_GET['sort']))
        {
            $sort = $_GET['sort'];

            switch ($sort['type'])
            {
                case 'rating':
                    $sort['dir'] = 'desc';
                    break;
                case 'date':
                case 'abc':
                case 'price':
                    $sort['dir'] = 'asc';
                    break;
            }

            $storage->setValue($namespace . 'sort', $sort);
            $page['sort'] = $sort;
        }
        else
        {
            $sort = in_array($namespace, array('places'))
                ? array(
                    'type' => 'rating',
                    'dir'  => 'desc'
                )
                : in_array($namespace, array('events'))
                ? array(
                    'type' => 'date',
                    'dir'  => 'asc'
                ) : false;
        }

        if (isset($_GET['filter']) || isset($_GET['sort']))
        {
            if (!$filter && $storage->getValue($namespace . 'filter'))
            {
                $filter = $storage->getValue($namespace . 'filter');   
            }

            if (!$sort && $storage->getValue($namespace . 'sort'))
            {
                $sort = $storage->getValue($namespace . 'sort');
            }
            
            if ((!isset($_GET['filter']) && $filter) || (!isset($_GET['sort']) && $sort))
            {
                $url = array(
                    'filter' => (array)$filter, 
                    'sort'   => (array)$sort
                );

                $router->go('?' . http_build_query($url));
            }
        }
        
        $page['filter'] = $filter;
        $page['sort'] = $sort;

        $page['clearFilter'] = $router->getLocation();
    }

    public function callback()
    {
        $result = array(
            'errors' => 0
        );

        if (!empty($_POST['phone']))
        {
            if (preg_match('#[^\d\(\)\-+\s]#im', $_POST['phone']))
            {
                $result['error']  = 'Номер телефона содержит недопустимые символы!';
            }
            else
            {
                if (empty($_POST['hours']))
                {
                    $result['error']  = 'Произошла ошибка! Дата невозможна!';
                }
                else
                {
                    $dt = new \DateTime();

                    switch($_POST['hours'])
                    {
                        case 'h1':
                            $dt = new \DateTime('+30 minutes');
                            break;
                        case 'h2':
                            $dt = new \DateTime('+1 hour');
                            break;
                        case 't1':
                            $dt = new \DateTime('today 15:00');
                            break;
                        case 'tm1':
                            $dt = new \DateTime('tomorrow 10:00');
                            break;
                        case 'tm2':
                            $dt = new \DateTime('tomorrow 15:00');
                            break;
                    }

                    \Core\Model\Callback::create($_POST['phone'], $dt);

                    if (empty($_POST['ajax']))
                    {
                        $this->router->reload();
                    }
                }
            }
        }
        else
        {
            $result['error']  = 'Вы не ввели номер телефона!';
        }

        echo json_encode($result);
    }

    public function forgotPassword()
    {
        $this->page['shopBanners']   = \Core\Model\Banner::shop(2);
        $this->page->display('forgotPassword.twig');
    }

    private function _flushPassword($values)
    {
        if (!empty($values['login']))
        {
            $user = \Core\Model\User::forgotPassword($values['login']);
            
            if ($user) 
            {
                $this->page->setMessage('Письмо с инструкциями по восстановлению отправлено на ваш email!');
                $this->router->go($this->router->generate('index'));
                return;
            }
        }

        $this->page->setMessage('Произошла ошибка: адрес не найден!');
        $this->router->reload();
    }    

    public function confirmFlushPassword($key)
    {
        $user = \Core\Model\User::confirmFlushPassword($key);

        $message = $user 
            ? 'Теперь вы можете пользоваться новым паролем!'
            : 'Произошла ошибка! Пароль не сброшен.';
        $this->page->setMessage($message);

        $this->router->go($this->router->generate('index'));
    }    
}