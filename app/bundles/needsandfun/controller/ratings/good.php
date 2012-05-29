<?php

namespace Needsandfun\Controller;

class RatingsGood extends \Core\Abstracts\Authorized
{
    private function _proceed($good, $values)
    {
        $message = \Core\Model\Review::add($good, $this->getClient(), $values)
            ? 'Ваш отзыв добавлен успешно! Он появится на сайте после модерации!'
            : 'При попытке добавить отзыв произошла ошибка!';

        $this->page->setMessage($message);
        $this->router->reload();
    }

    private function _categories($selected = false)
    {    
        $this->page['shopCategories'] = \Core\Model\Category::getAll($selected);
    }

    public function index($good)
    {
        $good = \Core\Model\Good::from_url($good);

        if (isset($_POST['proceed']))
        {
            $this->_proceed($good, $_POST);
        }
        else
        {
            $categoryId = $this->getStorage('flash')->getValue('categoryId');    
            $this->getStorage('flash')->setValue('categoryId', $categoryId);
            
            if (!$categoryId)
            {
                $categoryId = $good->getCategory();
            }
            
            $this->_categories($categoryId);

            $this->page['reviews'] = $good->reviews;
            
            if (!$good->deleted) $this->page['item'] = $good;
            $this->page->display('ratings/good.twig');
        }
    }
}