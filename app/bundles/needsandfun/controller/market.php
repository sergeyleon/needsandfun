<?php

namespace Needsandfun\Controller;

class Market extends \Core\Abstracts\Singleton
{
	public function clickAndPay()
	{
		$options = array(
			'conditions' => 'deleted is null 
				and id in (
					select 
						good_id 
					from goods_categories
					inner join categories on goods_categories.category_id = categories.id 
						and categories.deleted is null 
						and categories.is_visible=1)',
			'order' => 'name'
		);

		$this->page['goods']      = \Core\Model\Good::all($options);

		$options = array(
			'conditions' => 'deleted is null',
			'order' => 'name'
		);
		$this->page['categories'] = \Core\Model\Category::all($options);

		$this->page->display('market/clickandpay.twig');
	}
}