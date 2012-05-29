<?php

namespace Core\Model;

interface Reviewable
{
	/**
	 * возвращаем значение рейтинга
	 */
    public function rating();

    /**
	 * возвращаем отзывы 
	 */
    public function reviews();

    /**
	 * обновляем рейтинг после подтверждения
	 */
    public function updateRating();

    /**
     * возвращаем связывающую модель для отзыва 
     */
    public function getLinkModel();
}