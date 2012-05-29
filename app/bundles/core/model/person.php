<?php
/**
 * Модель пользователя. По своей сути является оберткой для User, 
 * с одним отличием — Person представляет собой незалогиненного пользователя тоже.
 *
 * @author Ilya Doroshin
 */
namespace Core\Model;

class Person extends \Core\Abstracts\Common
{

   /**
    * Переменная, в которой хранится модель текущего авторизованного пользователя
    * @var \Core\Model\User
    */
   private $_user;
   
   /**
    * Конструктор модели. Может принимать несколько значений: 
    * 1. логин-пароль
    * 2. айди пользователя в базе
    * 3. ничего - в данном случае, происходит проверка пользователя по его
    *    идентификационному токену
    */
   public function __construct()
   {
      $params = func_get_args();
      $user = false;
      
      switch (count($params))
      {
         # берем по логину-паролю
         case 2:
            list($login, $password) = $params;
            $user = $this->getUserByLoginAndPassword($login, $password);
            break;
         
         # берем по id
         case 1:
            list($userId) = $params;
            $user = $this->getUserById($userId);
            break;         
         
         # берем из авторизационных данных
         case 0:
            $token = $this->getStorage('storable')->getValue(AUTH_TOKEN);
            $user = $this->getUserByToken($token);
            break;
         
         # алертим ошибку
         default:
            throw new \Core\Error('Неопределенный выбор!');
            $user = false;
            break;
      }
      
      if ($user) 
      {
         $this->_user = $user;
      }
      else
      {
         $this->_user = false;
      }
   }
   
   /**
    * Инициализируем пользователя по логину и паролю. Используется при авторизации.
    * @param string $login
    * @param string $password
    * @return \Core\Model\User 
    */
   public function getUserByLoginAndPassword($login, $password)
   {
      $user     = false;
      $password = User::password($password);
      $user     = User::find_by_login($login);
      
      
      if ($user)
      {
          if ($password != $user->password)
          {
              $this->getStorage('flash')->setValue('auth_error', 'AUTH:BAD_PASSWORD');          
              $user = false;
          }          
      }
      else 
      {
          $this->getStorage('flash')->setValue('auth_error', 'AUTH:BAD_USER');
      }
      
      return $user; 
   }

   /**
    * Инициализируем пользователя по авторизационному токену. 
    * Используется по умолчанию для конструктора Person.
    * @param string $token
    * @return \Core\Model\User 
    */
   private function getUserByToken($token)
   {
      $user  = false;
      $token = Usertoken::find_by_token($token);       
      
      if ($token) 
      {
         $user = $token->user;
      }
      
      return $user; 
   }
   
   public function updateLastVisit()
   {
//       $this->_user->updateLastVisit(new \DateTime());
   }
   
   public function getLastVisit()
   {
//       return $this->_user->getOldLastVisit();
   }

   /**
    * Функция возвращает приватное значение, 
    * в котором хранится объект User
    *
    * @return \User $user
    */
   public function getUser()
   {
      return $this->_user;
   }   
   
   /**
    * Функция возвращает меню пользователя архивом
    * @todo сделать выбор меню из базы!
    * @return array 
    */
   public function getMenu()
   {
      $menu = $this->getRouter()->prepareMenu($this->_user->menu->links);

      return $menu;
   }
   
   /**
    * Выход из системы. По значению авторизационного ключа, находим пользователя
    * и очищаем ему авторизационные данные.
    * @param string $token 
    */
   public function logout($token)
   {
      $token = Usertoken::find_by_token($token);      

      if ($token) 
      {
         $token->delete();
      }
   }
   
   /**
    * Проверочная функция. Если пользователя не существует в базе — 
    * запоминаем текущее положение и отправляем на страницу авторизации.
    */
   public function mustBeLogged($updateLastVisit = true)
   {
      if (!$this->_user)
      {
         $route    = $this->getRouter()->getRoute();
         $location = $this->getRouter()->generate($route);
         $auth     = $this->getRouter()->generate('auth');

         $this->getStorage('session')->setValue('referer', $location);
         $this->getRouter()->go($auth);
      }
      else 
      {
          $this->_setToken($this->getStorage('storable')->getValue(AUTH_TOKEN));
          
          if ($updateLastVisit)
          {
              $this->updateLastVisit();
          }
      }
      
      return $this->_user;
   }
   
   public function auth()
   {   
       if ($this->_user)
       {
           $token = $this->_user->auth(); 
           $this->_setToken($token);       
       }
       else 
       {
           return false;   
       }
   }
   
   private function _setToken($token)
   {
        $this->getStorage('storable')->setValue(AUTH_TOKEN, $token);
   }
}
