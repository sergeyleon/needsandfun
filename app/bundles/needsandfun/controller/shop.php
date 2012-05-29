<?php

namespace Needsandfun\Controller;

class Shop extends \Core\Abstracts\Authorized
{
    public function __construct()
    {
        parent::__construct();
        Index::filters();
        $this->page['clearFilter'] = $this->router->generate('shop_index');
    }

    private function _getGoods($page = 1, $categories = false, $category = false)
    {
        $conditions = array(
            'goods.is_available = 1',
            'goods.deleted is null',
        );

        if ($categories)
        {
            $conditions[] = 'goods.id in (select good_id from goods_categories where category_id in (' . implode(', ', $categories) . '))';
        }
        
        if (!empty($_GET['filter'])) 
        {

            $result = array('conditions' => array(), 'variables' => array());
            $filter = $_GET['filter'];
            $this->page['filter'] = $filter;

            if (isset($filter['gender']))
            {
                $gender = $filter['gender'];

                if ('male' == $gender)
                {
                    $result['conditions'][] = 'sex in (0, 1)';    
                }
                else if ('female' == $gender)
                {
                    $result['conditions'][] = 'sex in (0, 2)';
                }
                else
                {
                    $result['conditions'][] = 'sex in (0, 1, 2)';
                }
            }

            if (isset($filter['age']))
            {
                $age  = $filter['age'];
                $ages = \Core\Model\Good::$age_ranges;

                if (isset($ages[$age]))
                {
                    $result['conditions'][] = '((age_to is not null and age_to >= ?) or age_to is null)';
                    $result['conditions'][] = '((age_from is not null and age_from < ?) or age_from is null)';


                    $result['variables'] = array_merge($result['variables'], $ages[$age]);
                }
            }

            if (isset($filter['brands']))
            {
                $result['conditions'][] = 'brand_id in (?)';
                $result['variables'][] = $filter['brands'];
            }

            $conditions = array_merge($conditions, $result['conditions']);
            $variables = $result['variables'];
        }

        $options = array('conditions' => array(implode(' AND ', $conditions)));
        
        if (!empty($variables))
        {
            $options['conditions'] = array_merge($options['conditions'], $variables);
        }

        $total = \Core\Model\Good::all($options);
        $this->page['pager']   = \Core\Model\Good::getPager($page, $category, $categories ? 'shop_category_page' : 'shop_index_page', $total);

        // echo count($total);

        $options['limit']  = \Core\Model\Good::$perPage;
        $options['offset'] = \Core\Model\Good::$perPage * ($page - 1);
        $options['order']  = 'name';

        if (isset($this->page['sort']))
        {   
            $sort = $this->page['sort'];

            $dir = isset($sort['dir']) && 'desc' == $sort['dir']
                ? 'DESC'
                : 'ASC';

            $order = array();

            if (isset($sort['type']))
            {
                switch ($sort['type'])
                {
                    case 'rating':
                        $order[] = 'rating ' . $dir;
                        break;

                    case 'price':
                        $options['joins'] = 'left join sizes on sizes.good_id = goods.id';
                        $options['group'] = 'goods.id';
                        $order[] = 'sizes.price ' . $dir;
                        break;

                    case 'created':
                        $order[] = 'goods.created ' . $dir;
                        break;
                }
            }

            $order[] = 'name ' . $dir;

            $options['order'] = implode(', ', $order);
        }

        $goods = \Core\Model\Good::all($options);

        if (count($total) > 0 && count($goods) == 0 && $page > 1)
        {
            $this->router->go($this->router->generate($categories ? 'shop_category_page' : 'shop_index_page', array('page' => 1)));
        }

        return $goods;
    }

    private function _categories($selected = false)
    {    
        $this->page['shopCategories'] = \Core\Model\Category::getAll($selected);
    }
    /**
     * показываем первую страницу каталога
     */
    public function index($page = 1)
    {
        $this->_categories();
        
        $this->page['bigBanners']  = \Core\Model\Banner::big();
        $this->page['brands']          = \Core\Model\Brand::all(array('conditions' => 'deleted is null'));

        if (!empty($_GET['filter'])) $this->page['goods'] = $this->_getGoods($page);
        else $this->page['newGoods'] = \Core\Model\Good::newGoods(\Core\Model\Good::$perPage);

        $this->page->display('shop/index.twig');
    }
    
    public function category($category, $page = 1) 
    {
        $this->page['currentCategory'] = \Core\Model\Category::from_url($category); 
        $category = $this->page['currentCategory']->encoded_key;

        $this->page['breadcrumbs']     = \Core\Model\Category::$breadcrumbs;
        $this->page['brands']          = \Core\Model\Brand::all(array('conditions' => 'deleted is null'));

        $categoryId = $this->page['currentCategory']->id;

        $filters = array('categoryId' => $categoryId);
        $this->getStorage('flash')->setValue('categoryId', $categoryId);
        $this->_categories($categoryId);
            
        $categories = $this->page['currentCategory']->getChildren();
        
        $this->page['goods'] = $this->_getGoods($page, $categories, $category);
        $this->page->display('shop/index.twig');
    }
    
    public function good($good)
    {
        $good = urldecode($good);
        
        $categoryId = $this->getStorage('flash')->getValue('categoryId');    
        $this->getStorage('flash')->setValue('categoryId', $categoryId);        

        $item = \Core\Model\Good::from_url($good);
        
        if (!$categoryId)
        {
            $categoryId = $item->getCategory();
        }
        
        $this->_categories($categoryId);
        
        if (!$item->deleted) $this->page['item'] = $item;
        $this->page->display('shop/good.twig');
    }
    
    private function _getPage($type)
    {
        $page = \Core\Model\Page::find(array('conditions' => array('page_type_id = ?', $type)));

        if ($page->isAboutPage())
        {
            $page->contents = json_decode($page->contents);
            $template = 'shop/about_page.twig';
        }
        else {
            $template = 'shop/page.twig';
        }

        $this->page['actual'] = \Core\Model\Event::thisWeek(4);

        $this->page['item'] = $page;
        $this->page['shopBanners']  = \Core\Model\Banner::shop(4);
        
        $this->page->display($template);
    }
    
    public function aboutPage()
    {
        if (isset($_POST['contact']))
        {
            $options = array();

            if (isset($_POST['content']) && !empty($_POST['content']))
            {
                $text = array();
                $sender = array();

                if (isset($_POST['name']))
                    $sender[] = $_POST['name'];

                if (isset($_POST['email']))
                    $sender[] = $_POST['email'];

                if (count($sender)) 
                    $text[] = '<p>Автор: ' . implode(', ', $sender) . '</p>';

                if (!empty($_POST['subject']))
                {
                    $text[] = '<p>Тема сообщения: ' . $_POST['subject'] . '</p>';
                }

                $text[] = '<p>Обращение: ' . $_POST['content'] . '</p>';

                $options['subject'] = 'Поступило новое обращение.';
                $options['to'] = $this->env->variables->MAIL->reciever;
                $options['text']    = implode("\n", $text);

                if (\Core\Model\Email::get()->create($options))
                {
                    $this->page->setMessage('Мы получили Ваше обращение. Оно будет рассмотрено в ближайшее время. Спасибо!');
                }
            }
            else {
                $this->page->setMessage('Нельзя отправить пустое обращение!');
            }

            $this->router->reload();
        }

        $this->_getPage(\Core\Model\Pagetype::$ABOUT);
    }

    public function deliveryPage()
    {
        $this->_getPage(\Core\Model\Pagetype::$DELIVERY);
    }

    public function paymentPage()
    {
        $this->_getPage(\Core\Model\Pagetype::$PAYMENT);
    }

    public function sizechartPage()
    {
        $this->_getPage(\Core\Model\Pagetype::$SIZECHART);
    }
}
