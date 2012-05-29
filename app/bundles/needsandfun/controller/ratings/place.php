<?php

namespace Needsandfun\Controller;

class RatingsPlace extends \Core\Abstracts\Authorized
{
    private function _proceed($place, $values)
    {
        $message = \Core\Model\Review::add($place, $this->getClient(), $values)
            ? 'Ваш отзыв добавлен успешно!<br />Он появится на сайте после модерации!'
            : 'При попытке добавить отзыв произошла ошибка!';

        $this->page->setMessage($message);
        $this->router->reload();
    }

    public function index($place)
    {
        $place = \Core\Model\Place::from_url($place);

        if (isset($_POST['proceed']))
        {
            $this->_proceed($place, $_POST);
        }
        else
        {
            $this->page['reviews'] = $place->reviews;

            if (2 < count($this->page['reviews'])) $limit = 4;
            else $limit = 3;

            $this->page['shopBanners'] = \Core\Model\Banner::shop($limit);
            
            if (!$place->deleted) $this->page['item'] = $place;
            $this->page->display('ratings/place.twig');
        }
    }
}