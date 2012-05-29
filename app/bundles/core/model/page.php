<?php

namespace Core\Model;

class Page extends \ActiveRecord\Model
{
    static $belongs_to = array(
        array('pagetype', 'foreign_key' => 'page_type_id')
    );    

    public function isAboutPage()
    {
    	return Pagetype::$ABOUT == $this->page_type_id;
    }

    public function isDeliveryPage()
    {
    	return Pagetype::$DELIVERY == $this->page_type_id;
    }

    public function isPaymentPage()
    {
    	return Pagetype::$PAYMENT == $this->page_type_id;
    }

    public function isSizechartPage()
    {
    	return Pagetype::$SIZECHART == $this->page_type_id;
    }

    public function deletable()
    {
    	return !$this->isSizechartPage() && !$this->isPaymentPage() && !$this->isDeliveryPage() && !$this->isAboutPage();
    }

    static function getAll()
    {
        return self::all();
    }
}