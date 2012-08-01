<?php

namespace Needsandfun\Controller;

class Brands extends \Core\Abstracts\Authorized
{
	public function index()
    {
        $this->page['brands'] = \Core\Model\Brand::all(array(
            'conditions' => 'deleted is null',
            'order'      => 'name'
        ));
        
        $this->page->display('brands.twig');
    }
    
    static function getPager($page, $category = false, $route, $total = 0)
    {
        $options = array();
        $routeParams['current'] = $page;

        if ($category)
        {
            $routeParams['brand'] = $category;
            $options['brand'] = $category;
        }

        $pager = array(
            'total'   => ceil(count($total)/self::$perPage),
            'current' => $page,
            'route'   => $route,
            'routeParams' => $routeParams
        );

        if ($pager['total'] == 1)
        {
            $pager = false;
        }

        return $pager;
    }
    
    
    private function _getGoods($page = 1, $brandId, $brand = false)
    {
    
    
    
$conditions = array(
            'goods.is_available = 1',
            'goods.deleted is null',
            'goods.brand_id = '.$brandId
        );


        
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


            $conditions = array_merge($conditions, $result['conditions']);
            $variables = $result['variables'];
        }

        $options = array('conditions' => array(implode(' AND ', $conditions)));
        
        if (!empty($variables))
        {
            $options['conditions'] = array_merge($options['conditions'], $variables);
        }    
    
    
/*
        $options = array('conditions' => array('goods.is_available = 1 and goods.deleted is null and goods.brand_id = ?', $brandId));
*/
        $this->page['clearFilter'] = $this->router->generate( 'brand_page', array('page' => 'all', 'brand' => $brand));
        
        if (is_numeric($page))
        {
            $total = \Core\Model\Good::all($options);

            $options['limit']  = \Core\Model\Brand::$perPage;
            $options['offset'] = \Core\Model\Brand::$perPage * ($page - 1);

            $this->page['pager']   = \Core\Model\Brand::getPager($page, $brand, 'brand_page' , $total);
        }
        
        $options['order']  = 'name';

        //if (isset($this->page['sort']))
        if (!empty($_GET['sort'])) 
        {   
        
        $sort = $_GET['sort'];
            $this->page['sort'] = $sort;

        
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
        
        

        if (isset($total) && count($total) > 0 && count($goods) == 0 && $page > 1)
        {
            $this->router->go($this->router->generate('brand_page', array('page' => 1)));
        }

        return $goods;
    }
    
    public function brand($brand, $page = 1) 
    {

    //    $this->page['breadcrumbs']     = \Core\Model\Category::$breadcrumbs;
        $this->page['brands']          = \Core\Model\Brand::all(array('conditions' => 'deleted is null'));

        $brand = explode("-page",$brand);
        $brand = $brand[0];
        $currentBrands = \Core\Model\Brand::all(array('conditions' => array('link = ?', $brand)));
        
        foreach ($currentBrands as $currentBrand) {
          $brandId = $currentBrand->id;
        }

        $this->page['goods'] = $this->_getGoods($page, $brandId, $brand);
        
        
        $this->page['brandbreadcrumbs'] = \Core\Model\Brand::all(array('conditions' => array('link = ?', $brand)));
        
        $this->page->display('shop/brand.twig');
    }
}