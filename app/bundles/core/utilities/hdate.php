<?php

/**
 * Класс для отображения времени по-человечески
 */
 
namespace Core\Utilities;
 
class hdate extends \DateTime
{
    private $_words = array(
        's' => array('секунда', 'секунды', 'секунд', 'секунду'),
        'i' => array('минута',  'минуты',  'минут',  'минуту'),
        'h' => array('час',     'часа',    'часов'),        
        'd' => array('день',    'дня',     'дней'),
        'w' => array('неделя',  'недели',  'недель', 'неделю'),        
        'm' => array('месяц',   'месяца',  'месяцев'),                
        'y' => array('год',     'года',    'лет'),
    );
    
    private $_set = array(
        array(
            array('через %2$s', 'через %d %s'),        # года, месяцы, часы, минуты, секунды
            array('завтра', 'через %d %s'),            # дни
            'меньше минуты'                            # если через < минуту 
        ),
        array(
            array('%2$s назад', '%d %s назад'),
            array('вчера', '%d %s назад'),            
            'меньше минуты назад'            
        ),
        'только что'
    );
    
    public function humanize($showTime = true)
    {        
        $now = new \DateTime();
        $diff = $now->diff($this);
        
        
        /**
         * @todo необходимо убрать эту надстройку, когда все поля с датами будут timestamp
         */
        $this->setTimezone(new \DateTimeZone('Europe/Moscow'));

        
        if ($diff->y)
        {
            $params = array(0, 1 !=$diff->y ?:0, $diff->y, $this->_lexer($diff->y, 'y'));
        }
        else if ($diff->m)
        {
            $params = array(0, 1 !=$diff->m ?:0, $diff->m, $this->_lexer($diff->m, 'm'));
        }
        else if ($diff->d)
        {
            $params = array(1, 1 !=$diff->d ?:0, $diff->d, $this->_lexer($diff->d, 'd'));
        }
        else if ($diff->h && !$showTime)
        {
            $params = array(0, 1 !=$diff->h ?:0, $diff->h, $this->_lexer($diff->h, 'h'));
        }
        else if ($diff->i && !$showTime)
        {
            $params = array(0, 1 !=$diff->i ?:0, $diff->i, $this->_lexer($diff->i, 'i', true));
        }
        else if ($diff->s && !$showTime)
        {
            $result = $this->_set[$diff->invert][2];
//            $params = array(0, 1 !=$diff->s ?:0, $diff->s, $this->_lexer($diff->s, 's', true));
        }
        else if ($now->format('ymdhis') == $this->format('ymdhis'))
        {
            $result = $this->_set[2];
        }
        else 
        {
            $result = $this->format('H:i');
        }
        
        if (isset($params))
        {
            $result = sprintf($this->_set[$diff->invert][$params[0]][$params[1]], $params[2], $params[3]);
        }
        
        return $result;
    }
    
    private function _lexer($number, $param, $dat = false) 
    {
        $mod10 = $number % 10;
        $mod100 = $number % 100; 
        
        if (!in_array($mod100, array(12, 13, 14)) and
            in_array($number, array(2, 3, 4))
         || in_array($mod10, array(2, 3, 4)))
        {
            $text = $this->_words[$param][1];                
        }
        else if (11 != $mod100 and 
              1 == $mod10 ||
              1 == $number ) 
        {
            $text = $this->_words[$param][$dat?3:0];        
        }
        else 
        {
            $text = $this->_words[$param][2];                    
        }
        
        return $text;
    }
}