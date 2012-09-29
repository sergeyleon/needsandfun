<?php

namespace Needsandfun\Controller;

class Market extends \Core\Abstracts\Singleton
{
	public function clickAndPay()
	{

/*
      $options = array(
      'conditions' => 'deleted is null 
				and is_available=1 
				and (supplier_id=13 or supplier_id=3 )',
            'select' => 'goods.*, categories.id as cat_id',
            'joins'  => 'inner join (
                select 
                    goods_categories.good_id, categories.id , goods_categories.category_id 
                from goods_categories
                left join categories on categories.id = goods_categories.category_id 
                order by goods_categories.category_id desc) as categories on categories.good_id = goods.id ' ,
        );
        $this->page['goods']      = \Core\Model\Good::all($options);

*/     
        
      $options = array(
        'select' => 'goods_categories.good_id, goods_categories.category_id, goods.*',
        'joins'  => 'inner join goods on goods.id = goods_categories.good_id and (goods.is_available = 1 or goods.in_stock>0 ) and goods.deleted is null and (goods.supplier_id=13 or goods.supplier_id=14 or goods.supplier_id=15 or goods.supplier_id=18 or goods.supplier_id=36 or goods.supplier_id=37 ) ' ,
      );

    
		$this->page['goods']      = \Core\Model\Goodcategory::all($options);



		$options = array(
			'conditions' => 'deleted is null',
			'order' => 'name'
		);
		$this->page['categories'] = \Core\Model\Category::all($options);
		
		

		$this->page->display('market/clickandpay.twig');
	}
}