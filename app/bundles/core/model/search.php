<?php
/**
 * Модель поиска.
 * @author Ilya Doroshin
 * @todo   сделать отсечку по заблокированным категориям
 */
namespace Core\Model;

class Search extends \Core\Abstracts\Singleton
{    
	public function find($query = false, $filter = false)
	{
		$search = array(
			'query'      => $query,
			'categories' => array(),
			'results' =>    array()
		);

		$query = '%' . $query . '%';

		if (!empty($filter['events']) && !empty($filter['articles']) && !empty($filter['places']) && !empty($filter['goods']))
		{
			$filter['all'] = true;
		}

		if (!$filter || !empty($filter['all']) || !empty($filter['articles']))
		{
			// статьи
			$articles = Article::all(array(
				'conditions' => array('(name like ? or content like ? or announce like ?) and deleted is null', $query, $query, $query),
				'order' => 'name',
				'limit' => 30
			));

			if (!empty($articles))
			{
				$search['categories'][] = array(
					'name'  => 'Статьи',
					'type'  => 'article',
					'count' => count($articles)
				);

				$search['results'] = array_merge($search['results'], $articles);
			}
		}

		if (!$filter || !empty($filter['all']) || !empty($filter['events']))
		{
			// события
			$events = Event::all(array(
				'conditions' => array('(name like ? or description like ?) and deleted is null', $query, $query),
				'order' => 'name',
				'limit' => 30
			));

			if (!empty($events))
			{
				$search['categories'][] = array(
					'name'  => 'События',
					'type'  => 'event',
					'count' => count($events)
				);

				$search['results'] = array_merge($search['results'], $events);
			}
		}

		if (!$filter || !empty($filter['all']) || !empty($filter['places']))
		{
			//  места
			$places = Place::all(array(
				'conditions' => array('(name like ? or description like ?) and deleted is null', $query, $query),
				'order' => 'name',
				'limit' => 30
			));

			if (!empty($places))
			{
				$search['categories'][] = array(
					'name'  => 'Места',
					'type'  => 'place',
					'count' => count($places)
				);

				$search['results'] = array_merge($search['results'], $places);
			}
		}

		if (!$filter || !empty($filter['all']) || !empty($filter['goods']))
		{
			// товары
			$goods = Good::all(array(
				'conditions' => array('(name like ? or description like ?) and deleted is null', $query, $query),
				'order' => 'name',
				'limit' => 30
			));

			if (!empty($goods))
			{
				$search['categories'][] = array(
					'name'  => 'Товары',
					'type'  => 'good',
					'count' => count($goods)
				);

				$search['results'] = array_merge($search['results'], $goods);
			}
		}

		if (!empty($filter['all']))
		{
			array_unshift($search['categories'], array(
				'name'  => 'Везде',
				'type'  => 'all',
				'count' => count($search['results'])
			));
		}

		
		return $search;
	}
}