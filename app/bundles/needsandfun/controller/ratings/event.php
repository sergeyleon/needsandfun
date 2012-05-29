<?php

namespace Needsandfun\Controller;

class RatingsEvent extends \Core\Abstracts\Authorized
{
    private function _proceed($event, $values)
    {
        $message = \Core\Model\Review::add($event, $this->getClient(), $values)
            ? 'Ваш отзыв добавлен успешно!<br />Он появится на сайте после модерации!'
            : 'При попытке добавить отзыв произошла ошибка!';

        $this->page->setMessage($message);
        $this->router->reload();
    }

    public function index($event)
    {
        $event = \Core\Model\Event::from_url($event);

        if (isset($_POST['proceed']))
        {
            $this->_proceed($event, $_POST);
        }
        else
        {
            $this->page['reviews'] = $event->reviews;

            if (2 < count($this->page['reviews'])) $limit = 4;
            else $limit = 3;

            if ($this->getClient())
            {
                $this->page['isMembered'] = $this->getClient()->isMember($event);
            }

            $this->page['shopBanners'] = \Core\Model\Banner::shop($limit);
            
            if (!$event->deleted) $this->page['item'] = $event;
            $this->page->display('ratings/event.twig');
        }
    }
}