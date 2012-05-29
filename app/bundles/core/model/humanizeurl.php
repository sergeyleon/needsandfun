<?php

namespace Core\Model;

interface Humanizeurl
{
	/**
	 * генерируем читабельную урлу
	 */
    public function get_url();

    /**
     * генерируем только ключ
     */
    public function get_encoded_key();

    /**
     * конвертируем читабельную урлу обратно в текст
     */
    static function from_url($url);

    /**
     * не забываем транслитерировать имя после изменения
     */
    public function updateLink();
}