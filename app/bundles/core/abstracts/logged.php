<?php

/**
 * Данный класс используется как родительский для контроллеров страниц,
 * пользователи на которых должны быть авторизованы для всех действий
 * независимо: для отправки формы, отображения страниц или простановки
 * кукиса. Для этого в конструкторе класса инициализируется текущий юзер.
 *
 * @author Илья Дорошин
 */

namespace Core\Abstracts;

class Logged extends Singleton
{
    protected $_user;
    
    public function __construct($calledMethod = false)
    {
        $person = new \Core\Model\Person();
        
        $person->mustBeLogged();
        $this->_user = $person->getUser();        
        
        $page = $this->getPage();
        $page['loggedUser'] = $this->_user;
        
        
        $page['userMenu'] = $this->_user->getMenu();

        $registry = $this->getRegistry();
        $registry['logged_user'] = $person;
    }
    
}

