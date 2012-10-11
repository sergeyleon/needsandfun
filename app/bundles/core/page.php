<?php

namespace Core;

class Page extends Abstracts\Singleton implements \ArrayAccess
{
    private $_twig = false;
    private $_inited = false;
    private $_variables = array(
        'defaults' => array()
    );
    
    private $_extends = array(
        'functions' => array(
            'path'  => '\Core\Page::getPathHelper',
            'mail'  => '\Core\Page::getMailHelper',
            'go'    => '\Core\Page::getGoHelper',                        
            'url'   => '\Core\Page::getUrlHelper'
        ),
        
        'filters'     => array(
            'replace'  => '\Core\Page::strReplaceFilter',
            'money'    => '\Core\Page::moneyFilter',            
            'truncate' => '\Core\Page::truncateFilter',
            'hdate'    => '\Core\Page::humanDate',
            'slashes'  => '\Core\Page::twigAddSlashes'
        )
    );
    
    /**
     * конструктор просто подгружает необходимые библиотеки и регистрирует лоадер
     */
    protected function __construct()
    {      
  		require LIBRARY . '/Twig/lib/Twig/Autoloader.php';
  		\Twig_Autoloader::register();  		
    }
    
    /**
     * инициализация шаблонизатора происходит непосредственно 
     * в момент отображения шаблона, чтобы была возможность
     * в реальном времени менять текущее окружение
     */
    private function _init()
    {

        if (!$this->_twig)
        {
            /**
             * инициализируем лоадер
             * все шаблоны грузятся из папки TEMPLATES
             */
      		$loader      = new \Twig_Loader_Filesystem(TEMPLATES);
      		
            /**
             * инициализируем шаблонизатор
             */      		
      		$this->_twig = new \Twig_Environment($loader, $this->_getSetting());
      		
      		/**
      		 * обрабатываем функциональные расширения: фильтры и методы
      		 */
            $this->_setExtends();
            
            /**
             * устанавливаем значения по-умолчанию
             */
            $this->setDefaults($this->_getDefaults()); 
      	}
    }
    
    private function _setExtends()
    {
        $extends = $this->_getExtends();

        foreach ($extends as $type => $ext)
        {
            switch ($type)
            {
                case 'filters':
                    foreach ($ext as $filter => $function)
                    {
                        if (is_array($function))
                        {
                            $function = $function[0];
                        }
                        
                        $this->_twig->addFilter($filter, new \Twig_Filter_Function($function));
                    }
                    break;
                    
               case 'functions':
                    foreach ($ext as $method => $function)
                    {
                        if (is_array($function))
                        {
                            $function = $function[0];
                        }
                        $this->_twig->addFunction($method, new \Twig_Function_Function($function));
                    }
                    break;                
            }
        }
        
        $settings = $this->_getSetting();
        
  		if (isset($settings['debug']) && $settings['debug'] === true)
  		{
  		    $this->_twig->addExtension(new \Twig_Extensions_Extension_Debug());
  		}
    }
    
    /**
     * обертка для забора настроек шаблонизатора
     * @return array массив с настройками
     */
    private function _getSetting()
    {
        $settings = array(
            'auto_reload' => true
        );

        if (!empty($this->env->variables->TWIG->cache))
        {
            $settings['cache'] = SITE . $this->env->variables->TWIG->cache;
        }

        return $settings;
    }    

    /**
     * обертка для забора настроек шаблонизатора
     * @return array массив с настройками
     */    
    private function _getExtends()
    {        
        return $this->_extends;
    }    
    
    /**
     * установка значений по-умолчанию, 
     * доступных из всех шаблонов
     *
     * @return array массив переменных по-умолчанию 
     */
    private function _getDefaults()
    {
        $months_short = array(
            'янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сент', 'окт', 'ноя', 'дек'
        );

        $months_full = array(
            1 => 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
        );

        
        $defaults = array(
            'root'        => WWW,
            'templates'   => $this->getEnvironment()->getPaths()->templates,                    # папка с шаблонами текущего окружения
            'common'      => $this->getEnvironment()->isRewrited()                              # папка с файлами фронтенда текущего окружения
                                ? COMMON . '/' . $this->getEnvironment()->getPaths()->common    
                                : '',

            'environment' => $this->getEnvironment()->variables,
            'dates'       => array(
                'today'     => new \DateTime(),
                'yesterday' => new \DateTime('-1 day'),                
                'tomorrow'  => new \DateTime('+1 day'),                
            ),

            'macros'      => '/common/macro',                                                   # путь к шаблонам макросов
            'layouts'     => '/common/layout',                                                  # путь к шаблонам layout            
            'shortMonths' => $months_short,
            'fullMonths'  => $months_full,
            'pager'       => array(
                'side'    => 5                                                                  # отступы от текущего значения
            )
        );

        return $defaults;
    }
    
    /**
     * установка значений по-умолчанию
     *
     * @var array $defaults массив с переменными
     */
    public function setDefaults($defaults = array()) 
    {
        $this->_variables['defaults'] = array_merge($this->_variables['defaults'], $defaults);    
        $this->_twig->addGlobal('defaults', $this->_variables['defaults']);
        
        $this->_twig->addGlobal('rootCategories', \Core\Model\Category::getAll());
    }
        
    public function setMessage($message)
    {
        $messages[md5($message)] = $message;        
        $old = (array)$this->flashStorage->getValue('popupMessages');

        if (!empty($old) && !empty($messages))
        {
            $messages = array_merge($old, $messages);
        }

        $this->flashStorage->setValue('popupMessages', $messages);
    }
    
    private function _getMessages() 
    {
        $messages = (array)$this->flashStorage->getValue('popupMessages');
        
        if (count($messages))
        {
            array_shift($messages);
            $this->_variables['popupMessages'] = $messages;
        }
    }
        
    /**
     * Генерация html-кода шаблонов
     * @var   string   $template   имя файла шаблона
     * @var   array    $data       массив с переменными
     */
    public function render($template, $data = array())
    {
        $this->_getMessages();
        
        /**
         * инициализируем весь движок
         */
        $this->_init();
            
        $template = $this->getEnvironment()->getPaths()->templates . '/' . $template;
        
        if (is_callable(array($this->_twig, 'loadTemplate')))
        {
            $template = $this->_twig->loadTemplate($template);    
            $html = $template->render(array_merge($this->_variables, $data));
        }
        
        return $html;
    }

    /**
     * Отображение сгенерированного шаблонов
     * @var   string   $template   имя файла шаблона
     * @var   array    $data       массив с переменными
     */
    public function display($template, $data = array())
    {    
        /**
         * генерируем шаблон
         */
        $html = $this->render($template, $data);
        
        /**
         * отправляем хидер
         */
        header('Content-Type: text/html; charset=utf-8');
        
        /**
         * показываем шаблон
         */
        echo $html;
    }
    
    /**
     * Фильтр строк обертка для str_replace
     * @return string
     */
    static function strReplaceFilter($string, $search, $replace)
    {
        return str_replace($search, $replace, $string);
    }
    
    static function moneyFilter($number)
    {
        $pattern = '@';
        $number = number_format($number, 0, ',', $pattern) . '.-';
        $number = str_replace($pattern, ' ', $number);
        return $number;
    }    
    
    /**
     * Фильтр строк обертка для addslashes
     * @return string
     */
    static function twigAddSlashes($string)
    {
        return addslashes($string);
    }    
    
    /**
     * Фильтр строк обертка для substr
     * @return string
     */
    static function truncateFilter($string, $max, $tail = '…', $striptags = false)
    {
        $string = strip_tags(htmlspecialchars_decode($string));
        $string = mb_substr($string, 0, $max, 'utf8') . (mb_strlen($string)>$max ? $tail : '');
        return $string;
    }
    
    /**
     * Фильтр строк обертка для substr
     * @return string
     */
    static function humanDate($dt, $time = false)
    {
        $now = new \DateTime();

        $months = array(1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
        $date   = new \DateTime($dt);
        $result = $date->format('j') . ' ' .$months[$date->format('m')/1];

        if ($now->format('Y') != $date->format('Y') || 1)
        {
            $result .= ' ' . $date->format('Y');
        }

        if ($time)
        {
            $result .= ', ' . $date->format('H:i');
        }

        return $result;
    }    
    
    /**
     * Хелпер для вывода относительного пути роута
     * @return string
     */
    static function getPathHelper($route, $params = array())
    {
        return \Core\Router::get()->generate($route, $params);
    }
    
    /**
     * Хелпер для вывода mailto-запроса
     * @return string
     */
    static function getMailHelper($mail)
    {
        $mail = trim($mail);
        return 'mailto:' . $mail;
    }
    
    /**
     * Хелпер для вывода mailto-запроса
     * @return string
     */
    static function getGoHelper($address)
    {
        $address = trim($address);
        
        if (!preg_match('#^http\:\/\/#', $address))
        {
            $address = 'http://' . $address;
        }
        return $address;    
    }    
    
    
    /**
     * Хелпер для вывода абсолютного пути роута
     * @return string
     */
    static function getUrlHelper($route, $params = array())
    {
        return \Core\Router::get()->generate($route, $params, true);
    }
    
    /**
     * ARRAY_ACCESS
     */
    public function offsetExists($offset)
    {
        return isset($this->_variables[$offset])
            ? true
            : false;
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset)
            ? $this->_variables[$offset]
            : false;
    }

    public function offsetSet($offset, $value)
    {
        $this->_variables[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset))
        {
            unset($this->_variables[$offset]);
        }
    }

}