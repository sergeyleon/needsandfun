<?php 

namespace Needsandfun\Controller;

class Register extends \Core\Abstracts\Authorized
{
    private $_errors = array(
        'EMPTY_EMAIL'        => 'Вы не указали почтовый ящик!',
        'EMPTY_PASSWORD'     => 'Вы не указали пароль!',
        'PASSWORD_NOT_MATCH' => 'Указанные пароли не совпадают!',
        'SHORT_PASSWORD'     => 'Указанный пароль слишком короткий!',
        'USER_EXISTS'        => 'Пользователь с таким имейлом уже существует!'
    );
    public function __construct()
    {
        parent::__construct();

        if (isset($_POST['register']))
        {
            $this->_register($_POST);
        }
    }
    
    public function show()
    {    
        $this->page['shopBanners']   = \Core\Model\Banner::shop(2);
        $this->page->display('register.twig');
    }
    
    private function _register($values) 
    {
        $error = false;

        if (empty($values['login'])) 
        {
            $error = 'EMPTY_EMAIL';
        }
        else if (empty($values['password']))
        {
            $error = 'EMPTY_PASSWORD';
        }
        else if ($values['password'] != $values['repassword'])
        {
            $error = 'PASSWORD_NOT_MATCH';    
        }
        else if (mb_strlen($values['password']) <= 3)
        {
            $error = 'SHORT_PASSWORD';
        }
        else 
        {
            if (\Core\Model\User::exists($values['login']))
            {
                $error = 'USER_EXISTS';
            }
        }
        
        if ($error)
        { 
            $this->page->setMessage($this->_errors[$error]);
            $path = 'register';
        }
        else 
        {
            $user = \Core\Model\User::register($values['login'], $values['password']);

            if ($user)
            {                    
                $this->page->setMessage('Письмо с данными для активации вашей учетной записи отправлено!');
                $path = 'index';
            }
            else
            {
                $path = 'register';

            }
        }

        $this->router->go($this->router->generate($path));
    }
}