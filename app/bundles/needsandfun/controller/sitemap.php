<?php

namespace Needsandfun\Controller;

class Sitemap extends \Core\Abstracts\Singleton
{
	private $_pages = array();

	public function display()
	{
		$this->_addPages('index');
		
		$this->_addPages('pages');
		$this->_addPages('articles');

		$this->_addPages('shop_categories');
		$this->_addPages('shop_items');

		$this->_addPages('places_categories');
		$this->_addPages('places_events');		

		$this->_addPages('events_categories');
		$this->_addPages('events');

		$this->page['pages'] = $this->_pages;
		$this->page->display('sitemap.twig');
	} 

	private function _addPages($pages)
	{
		$now = new \DateTime();
		switch ($pages)
		{
			case 'index':
				$this->_pages[] = array(
					'location' => $this->router->generate('index', null, true),
					'priority' => '0.8',
					'lastmod'  => $now,
					'frequency' => 'daily'
				);
				break;

			case 'articles':
				$articles = \Core\Model\Article::getAll();
				foreach ($articles as $article)
				{
					$this->_pages[] = array(
						'location' => $this->router->generate('articles_article', array('article' => $article->encoded_key), true),
					);
				}
				break;

			case 'pages':
				$pages = \Core\Model\Page::getAll();
				foreach ($pages as $page)
				{
					$params = null;

					if ($page->isAboutPage())
					{
						$route = 'shop_about';
					}
					else if ($page->isDeliveryPage())
					{
						$route = 'shop_delivery';
					}
					else if ($page->isPaymentPage())
					{
						$route = 'shop_payment';
					}
					else if ($page->isSizechartPage())
					{
						$route = 'shop_size_chart';
					}
					else {
						$route = 'pages_show';
						$params = array('link' => $page->link);
					}

					$this->_pages[] = array(
						'location' => $this->router->generate($route, $params, true),
					);
				}
				break;				


			case 'places_categories':
				$categories = \Core\Model\Placecategory::getAll();
				foreach ($categories as $category)
				{
					$this->_pages[] = array(
						'location' => $this->router->generate('places_category', array('category' => $category->encoded_key), true),
					);
				}
				break;

			case 'places':
				$places = \Core\Model\Place::getAll();
				foreach ($places as $place)
				{
					$this->_pages[] = array(
						'location' => $this->router->generate('places_place', array('place' => $place->encoded_key), true),
					);

					$this->_pages[] = array(
						'location' => $this->router->generate('places_ratings_index', array('place' => $place->encoded_key), true),
					);
				}
				break;

			case 'events_categories':
				$categories = \Core\Model\Eventcategory::getAll();
				foreach ($categories as $category)
				{
					$this->_pages[] = array(
						'location' => $this->router->generate('events_category', array('category' => $category->encoded_key), true),
					);
				}
				break;

			case 'events':
				$events = \Core\Model\Event::getAll(array('from' => $now));
				foreach ($events as $event)
				{
					$this->_pages[] = array(
						'location' => $this->router->generate('events_event', array('event' => $event->encoded_key), true),
					);

					$this->_pages[] = array(
						'location' => $this->router->generate('events_ratings_index', array('event' => $event->encoded_key), true),
					);
				}
				break;

			case 'shop_categories':
				$categories = \Core\Model\Category::getAll();
				foreach ($categories as $category)
				{
					$this->_pages[] = array(
						'location' => $this->router->generate('shop_category', array('category' => $category->encoded_key), true),
					);
				}
				break;

			case 'shop_items':
				$items = \Core\Model\Good::getAll();
				foreach ($items as $item)
				{
					$this->_pages[] = array(
						'location' => $this->router->generate('shop_good', array('good' => $item->encoded_key), true),
					);

					$this->_pages[] = array(
						'location' => $this->router->generate('shop_good_ratings_index', array('good' => $item->encoded_key), true),
					);
				}
				break;
		}
	}
}