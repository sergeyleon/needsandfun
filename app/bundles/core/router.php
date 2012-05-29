<?php

/**
 * Класс роутер. Синглтон.
 * Обрабатывает все входящие запросы, cookie, get, post.
 * Подключает необходимые контроллеры.
 *
 * @author Ilya Doroshin
 *
 * @TODO: сделать flash-messages
 */

namespace Core;

class Router extends Abstracts\Singleton
{
   /**
    * текстовые коды http-статусов
    * @var array
    */
   private $_statuses = array(
      301 => 'Moved Permanently',
      401 => 'Unauthorized',
      403 => 'Forbidden',
      404 => 'Not Found'
   );
   
   private $_environment = false;

   /**
    * @var string текущий урл
    */
   private $_location = false;
   
   /**
    * массив со всеми загруженными роутами
    * @var array
    */
   private $_routes;  
   
   /**
    * имя текущего роута
    * @var string
    */
   private $_route = false;

   /**
    * текущий домен
    * @var string
    */
   private $_host = false;   
   
   /**
    * массив с параметрами
    * @var array 
    */
   private $_params = array();

   /**
    * конструктор роутера
    */
   protected function __construct($host = false)
   {
      $this->_host = $_SERVER['HTTP_HOST'];
      $this->_setup();
   }

   /**
    * сетап роутера
    */
   private function _setup()
   {
      $this->_routes();
      $this->_location();
   }

   /**
    * тащим все роуты из конфига
    */
   private function _routes()
   {
      $routes = Config::load('routing.yml');
      $this->_routes = $this->_parseRoutes($routes);
   }
   
   private function _parseRoutes($_routes)
   {
       $routes = new \StdClass;
       foreach ($_routes as $name => $route)
       {
           if (isset($route->source))
           {
               foreach ($this->_parseRoutes(Config::load($route->source)) as $_name => $_route)
               {
                   if (isset($route->environment))
                   {
                       $_route->environment = $route->environment;
                   }
                   $_route->pattern = $route->pattern . $_route->pattern;
                   
                   $route_name = isset($route->appendName) && $route->appendName
                       ? $name . '_' . $_name
                       : $_name;
                       
                   $routes->$route_name = $_route;
               }
           }
           else 
           {
               $routes->$name = $route;
           }
       }
       
       return $routes;
   }

   /**
    * обрабатываем текущий урл, записываем его в $this->_location
    */
   private function _location()
   {
      if (isset($_SERVER['REQUEST_URI']))
      {
         $location = explode('?', $_SERVER['REQUEST_URI']);
         $this->_location = str_replace(WWW, '', $location[0]);
      }
   }
   
   /**
    * обрабатываем роуты
    */
   public function execute()
   {
      $result = false;
      foreach ($this->_routes as $routeName => $route)
      {
         $matches = $this->_parsePattern($route);
         $pattern = $this->_preparePattern($route->pattern);
         
         foreach ($matches as $params)
         {
            foreach ($params as $param)
            {                  
               $preg = isset($route->requirements->$param)
                           ? $route->requirements->$param 
                           : (isset($route->defaults->$param) 
                               ? $route->defaults->$param
                               : '([^\/]+)');

               $pattern = str_replace('{' . $param . '}', $preg, $pattern);
            }
         }
         
         
         /**
          * проверяем текущий урл на наличие слеша в конце
          */
         if ('/' != substr($this->_location, -1, 1) && preg_match('#^' . $pattern . '$#', $this->_location . '/', $matches)) 
         {
             array_shift($matches);
             
             foreach ($matches as $key => $match)
             {
                 if (!$match) unset($matches[$key]); 
             }
             
             if (!count($matches))
             {
                 $this->getRouter()->go($this->_location . '/');    
             }
         }
         
         if (preg_match('#^' . $pattern . '$#', $this->_location, $matches))
         {
            array_shift($matches);

            if (isset($params))
            {
               foreach ($params as $key => $param)
               {
                  $this->_params[$param] = isset($matches[$key]) && $matches[$key] != ''
                     ? $matches[$key]
                     : (isset($route->defaults->$param) ? $route->defaults->$param : null);
               }
            }

            if ($route->defaults->controller)
            {
               $this->_route = $routeName;
               
               if (isset($route->environment))
               {
                   $this->getEnvironment()->setByNamespace($route->environment);
               }
               
               $__controller = explode(':', $route->defaults->controller);

               if (isset($__controller[0]) && isset($__controller[1]))
               {
                  list($controller, $method) = $__controller;                  
               }
               else
               {
                  throw new Error('Не указана функция вызова в роуте ' . $routeName . ': ' . $route->defaults->controller);
               }    
               
               $className = $this->getEnvironment()->getNamespace() . '\Controller\\' . $controller;
               
               if (is_callable($className . '::' . $method))
               {
                  $_params = array();
                  $reflection = new \ReflectionMethod($className, $method);

                  foreach ($reflection->getParameters() as $parameter)
                  {
                      $_param = $parameter->name;

                      if (isset($this->_params[$_param]))
                      {
                          $_params[] = $this->_params[$_param];
                      }
                      else if (isset($route->defaults->$_param)) 
                      {
                          $_params[] = $route->defaults->$_param;  
                      }
                  }
                  return $this->_callController($className, $method, $_params);
               }
               else
               {
                  throw new Error('Указанный метод не найден ' . $routeName . ': ' . $route->defaults->controller);
               }
            }
            else
            {
               throw new Error('Для данного роутера не указан контроллер: ' . $routeName);
            }

            $result = true;
         }
      }
      
      if (!$result)
      {
         $this->errorPage();
      }
   }

   /**
    * парсим паттерн
    * 
    * @param string $route
    * @return array 
    */
   private function _parsePattern($route)
   {
      $pattern = $this->_preparePattern($route->pattern);
      preg_match_all('#(?<=\{)[^\}]+(?=\})#', $pattern, $matches, PREG_PATTERN_ORDER);
      
      return $matches;
   }
   
   /**
    * подготавливаем паттерн
    * 
    * @param string $pattern
    * @return string 
    */
   private function _preparePattern($pattern)
   {
      return $pattern = str_replace('/', '\/', $pattern);
   }
   
   /**
    * подключаем контроллер
    * 
    * @param string $function
    * @param string $method
    * @param array  $params 
    */
   private function _callController($function, $method, $params = array())
   {   
      $controller = call_user_func($function . '::get', $method);
      call_user_func_array(array($controller, $method), $params);
   }

   /**
    * Возвращаем приватное свойство this->_location
    * @return string
    */
   public function getLocation()
   {
      return $this->_location;
   }
   
   /**
    * перезагружаем страницу 
    */
   public function reload()
   {
      $this->go(WWW . $this->_location);
   }

   /**
    * Переход по определенному урл
    * Можно указать http-статус 
    * 
    * @param string $location
    * @param number $status 
    */
   public function go($location, $status = null)
   {
      if ($status)
      {
         header('HTTP/1.1 ' . $status . ' ' . $this->statuses[$status]);
      }
      header('Location: ' . $location);
   }  
   
   /**
    * Показываем страницу с ошибкой
    * @param number $status 
    */
   public function errorPage($status = 404)
   {
      header('HTTP/1.1 ' . $status . ' ' . $this->_statuses[$status]);
      $this->getEnvironment()->set('default');
      $this->getPage()->display('errors/' . $status . '.twig');
   }
   
   /**
    * Функция генерирует нужный урл для указанного роута
    * можно передать параметры, которые нужно подставить
    * и указать, должна ли результирующая ссылка быть абсолютной
    * 
    * @param string $route
    * @param array $_params
    * @param boolean $absolute
    * @return string
    */
   public function generate($route, $_params = array(), $absolute = false)
   {
      $url = false;
      if (isset($this->_routes->$route))
      {
         $matches = $this->_parsePattern($this->_routes->$route);
         $url = $this->_routes->$route->pattern;
         
         foreach ($matches as $params)
         {
            foreach ($params as $param)
            {
               $value = isset($_params[$param]) 
                  ? $_params[$param]
                  : '';
               
               $url = str_replace('{' . $param . '}', $value, $url);
            }
         }
      }

      if (!$url)
      {
         throw new Error('Не найден роут! ' . $route);
      }
      else {
         $url = WWW . $url;
      }

      if ($absolute)
      {
          $url = 'http://' . $this->getHost() . $url;
      }

      if (!empty($_SERVER['QUERY_STRING']))
      {
        $url .= '?' . $_SERVER['QUERY_STRING'];
      }

      return $url;
   }

   /**
    * возвращаем приватное свойство $this->_host
    * @return type 
    */
   public function getHost()
   {
      return $this->_host;
   }
   
   /**
    * возвращаем приватное свойство $this->_route
    * @return type 
    */
   public function getRoute()
   {
      return $this->_route;
   }

   /**
    * возвращаем приватное свойство $this->_params
    * @return type array
    */   
   public function getParams()
   {
      return $this->_params;
   }
   
   /**
    * возвращаем приватное свойство $this->_routes
    * @return type 
    */
   public function getRoutes()
   {
      return $this->_routes;
   }   
   
   public function getMenu(\Core\Model\Menu $menu = null)
   {
        if (null === $menu) return;
        
        $address = $links = array();
        $router  = \Core\Router::get();
        
        foreach ($menu->menulinks as &$_menulink)
        {
            if (isset($address[$_menulink->id]))
            {
                if (isset($address[$_menulink->id]->children))
                {
                    $_menulink->children = $address[$_menulink->id]->children;
                    $address[$_menulink->id] = &$_menulink;
                }
            }
            else 
            {
                $address[$_menulink->id] = &$_menulink;            
            }

            $current = $this->getRoute() == $_menulink->link->route;
            
            if ($current)
            {
                $_menulink->current  = true;         
                $_menulink->selected = true;                
            }
            
            if ($_menulink->parent_id)
            {
                if (!isset($address[$_menulink->parent_id]->children))
                {
                    $address[$_menulink->parent_id]->children = array();
                }
                
                if ($current)
                {
                    $address[$_menulink->parent_id]->selected = true;
                }
               
                array_push($address[$_menulink->parent_id]->children, &$_menulink);
            }
            else 
            {
                array_push($links, &$_menulink);
            }
        }
        
        return $links;
   }
}

