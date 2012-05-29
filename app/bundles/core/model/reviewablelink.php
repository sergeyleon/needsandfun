<?php

namespace Core\Model;

interface Reviewablelink
{
	/**
	 * возвращаем тип ссылки
	 */
    public function getName();

    /**
     * генерим ссылку в клиентский сайт
     */
    public function getLink();

    /**
     * возвращаем ссылку на родителя
     */
    public function get_item();
}