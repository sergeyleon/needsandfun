<?php

namespace Needsandfun\Controller;

class Shop extends \Core\Abstracts\Authorized
{
    public function __construct()
    {
        parent::__construct();
        Index::filters();
        
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

                if ('1' == $gender)
                {
                    $result['conditions'][] = 'sex in (0, 1)';    
                }
                else if ('2' == $gender)
                {
                    $result['conditions'][] = 'sex in (0, 2)';
                }
                else
                {
                    $result['conditions'][] = 'sex in (0, 1, 2)';
                }
            }

            if (isset($filter['age_from']) and isset($filter['age_to']))
            {
                //$age  = $filter['age'];
                //$ages = \Core\Model\Good::$age_ranges;
                $age_ranges = array(
                    $filter['age_from'],
                    $filter['age_to']
                );
                $ages = $age_ranges;
                
                 //$conditions[] = ' age_from >= ' . $age_from. ' AND age_to <= '. $age_to ;

                

                if (isset($ages))
                {
                    $result['conditions'][] = '((age_to is not null and age_to >= ?) or age_to is null)';
                    $result['conditions'][] = '((age_from is not null and age_from < ?) or age_from is null)';


                    $result['variables'] = array_merge($result['variables'], $ages);
                }
            }

            if (isset($filter['brands']))
            {
            if($filter['brands'] != '0') {
                $result['conditions'][] = 'brand_id in (?)';
                $result['variables'][] = $filter['brands'];
                }
            }


            $conditions = array_merge($conditions, $result['conditions']);
            $variables = $result['variables'];
            
            
        }

        $options = array('conditions' => array(implode(' AND ', $conditions)));
        
        if (!empty($variables))
        {
            $options['conditions'] = array_merge($options['conditions'], $variables);
        }

        $this->page['clearFilter'] = $this->router->generate($categories ? 'shop_category_page' : 'shop_index_page', array('page' => 'all', 'category' => $category));
        
        if (is_numeric($page))
        {
            $total = \Core\Model\Good::all($options);

            $options['limit']  = \Core\Model\Good::$perPage;
            $options['offset'] = \Core\Model\Good::$perPage * ($page - 1);

            $this->page['pager']   = \Core\Model\Good::getPager($page, $category, $categories ? 'shop_category_page' : 'shop_index_page', $total);
        }
        
        

//////////
if (!empty($_GET['filter'])) 
        {

            $filter = $_GET['filter'];
            
        if (isset($filter['sort']))  {

            $sort  = $filter['sort'];
            $dir = isset($sort) && 'desc' == $sort
                ? 'DESC'
                : 'ASC';

            $order = array();

            if (isset($sort))
            {
 
               // switch ($sort)
              
               // {
                   /* case 'rating':
                        $order[] = 'rating ' . $dir;
                        break; */

                    //case 'price':
                        $options['joins'] = 'left join sizes on sizes.good_id = goods.id';
                        $options['group'] = 'goods.id';
                        $order[] = 'sizes.price ' . $dir;
                      //  break;

                    /* case 'created':
                        $order[] = 'goods.created ' . $dir;
                        break; */
                        
                    /* case 'abc':
                        $order[] = 'name ' . $dir;
                        break; */
               // }
            }



            $options['joins'] = 'left join sizes on sizes.good_id = goods.id';
            $options['group'] = 'goods.id';
            $order[]  = 'sizes.price DESC';
            
         
            $order[] = 'name ' . $dir;

            $options['order'] = implode(', ', $order);
        }
        
        }
 /////////////////       

        

        $goods = \Core\Model\Good::all($options);
        

        if (isset($total) && count($total) > 0 && count($goods) == 0 && $page > 1)
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
      
      //Baltic It fix
        $link = 'store';
        $page = \Core\Model\Page::find(array('conditions' => array('link = ?', $link)));
        $this->page['item'] = $page;
      //End Baltic It fix

    
        $this->_categories();
        
        $this->page['bigBanners']  = \Core\Model\Banner::big();
        $this->page['brands']          = \Core\Model\Brand::all(array('conditions' => 'deleted is null ORDER BY name ASC'));

        if (!empty($_GET['filter'])) $this->page['goods'] = $this->_getGoods($page);
        else $this->page['newGoods'] = \Core\Model\Good::newGoods(\Core\Model\Good::$perPage);

        $this->page->display('shop/index.twig');
    }
    
    public function category($category, $page = 1) 
    {
        $this->page['currentCategory'] = \Core\Model\Category::from_url($category);
        $category = $this->page['currentCategory']->encoded_key;

        $this->page['breadcrumbs']     = \Core\Model\Category::$breadcrumbs;
        //$this->page['brands']          = \Core\Model\Brand::all(array('conditions' => 'deleted is null'));



        $categoryId = $this->page['currentCategory']->id;

        $filters = array('categoryId' => $categoryId);
        $this->getStorage('flash')->setValue('categoryId', $categoryId);
        $this->_categories($categoryId);
            
        $categories = $this->page['currentCategory']->getChildren();

        
        // var_dump( $categories);
        
        $this->page['goods'] = $this->_getGoods($page, $categories, $category);
        
        //////////////////////////////////////// Brands in category
        foreach ($this->_getGoods($page, $categories, $category) as $good) 
        {
          $brandArray[] = ' id = '.$good->brand_id;
        }
        $brandArray = array_unique($brandArray);
        
        $options =  implode(' OR ', $brandArray);

        $this->page['brands'] = \Core\Model\Brand::all(array('conditions' => array($options.' ORDER BY name ASC ')));
        ////////////////////////////////////////
        
        //////////////////////////////////////// Child in category
        foreach ($categories as $categorie) 
        {
          $categoriesArray[] = ' id = '.$categorie;
        }
        $options2 =  implode(' OR ', $categoriesArray);

        $this->page['categories'] = \Core\Model\Category::all(array('conditions' => array($options2)));
        ////////////////////////////////////////
        
        // Crumbs
        $parentId = $this->page['currentCategory']->parent_id;
        $crumbsArray[] = $categoryId;
        
        if($parentId != '') {
        
        $this->page['crumbs'] = $this->crumbs($parentId, $crumbsArray);
          foreach ($this->page['crumbs'] as $crumb) 
          {
            $cArray[] = ' id = '.$crumb;
          }
        
          $options3 =  implode(' OR ', $cArray);
        }
        
        else { $options3 = ' id = '.$categoryId; }

        $this->page['breadcrumbs'] = \Core\Model\Category::all(array('conditions' => array($options3)));
        
        $this->page['page'] = $page;
        
        $this->page->display('shop/index.twig');
    }
    
    private function crumbs($pid,$crumbsArray)
    {
      $cat = \Core\Model\Category::all(array('conditions' => array('id = ?', $pid)));
     
      $crumbsArray[] = $cat[0]->id;

      if(!is_null($cat[0]->parent_id)) {
        return $this->crumbs($cat[0]->parent_id,$crumbsArray);
      }

      return $crumbsArray; 
    }
    
    
    private function _proceed($good, $values)
    {
        if(isset($values['code'])) {
        
           $message = \Core\Model\Review::add_private($good, 0, $values)
            ? 'Ваш отзыв добавлен успешно! Он появится на сайте после модерации!'
            : 'Вы ввели неверный код с картинки!';
        }
        else {
          $message = \Core\Model\Review::add($good, $this->getClient(), $values)
            ? 'Ваш отзыв добавлен успешно! Он появится на сайте после модерации!'
            : 'При попытке добавить отзыв произошла ошибка!';
        }

        $this->page->setMessage($message);
        $this->router->reload();
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
        
        // baltic it
        if (isset($_POST['proceed']))
        {
            $this->_proceed($item, $_POST);
        }
        
        $this->page['reviews'] = $item->reviews;
        // baltic it
        
        if (!$item->deleted) $this->page['item'] = $item;
        
        
        // Crumbs
        
        $cat = \Core\Model\Category::all(array('conditions' => array('id = ?', $categoryId)));
        $categoryId = $cat[0]->id;
        $parentId = $cat[0]->parent_id;
        
        $crumbsArray[] = $categoryId;
        
        if($parentId != '') {
        
        $this->page['crumbs'] = $this->crumbs($parentId, $crumbsArray);
          foreach ($this->page['crumbs'] as $crumb) 
          {
            $cArray[] = ' id = '.$crumb;
          }
        
          $options3 =  implode(' OR ', $cArray);
        }
        
        else { $options3 = ' id = '.$categoryId; }

        $this->page['breadcrumbs'] = \Core\Model\Category::all(array('conditions' => array($options3)));
        
        
        // Колво проданных
        $good_sizes = \Core\Model\Size::all(array('conditions' => array('good_id = ? ', $item->id )));
        $i = 0;
        foreach ( $good_sizes as $good_size) { 
          $order_goods = \Core\Model\Ordergood::all(array('conditions' => array('size_id = ? ', $good_size->id )));
          
          if(count($order_goods) > 0 ) {
            foreach ( $order_goods as $order_good) {
              $order_status = \Core\Model\Orderstatus::all(array('conditions' => array('order_id = ? and status_id = ?', $order_good->order_id,4 )));
              if (count($order_status) > 0 ) { $i++; }
            }

          }
        }
        
        $item->sell_amount = $i;
        $item->save();
        // Колво проданных
        
        
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