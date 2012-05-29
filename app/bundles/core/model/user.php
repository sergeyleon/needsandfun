<?php

/**
 * Пользователи системы управления заказами
 */
namespace Core\Model;

class User extends \ActiveRecord\Model
{   
    static $has_many = array(
        array('usertoken', 'foreign_key' => 'user_id'),
    );

    static $has_one = array(
        array('client')
    );
    
    public function logout(\Core\Model\Usertoken $token)
    {
        $token->flush();
    }

    static function prepare($options = array())
    {
        die('PREPARE?');
        
        $user = empty($options['user_id'])
            ? new self()
            : self::find($options['user_id']);
        
        $password       = !empty($options['password'])   ?: self::generatePassword();
        $user->password = md5($password);        
        
        $user->login    = !empty($options['login'])      ?: 'guest' . (count(self::all())+1);
        $user->blocked  = !empty($options['is_blocked']) ?: true;
        $user->save();
        
        return $user;
    }

    static function exists($email)
    {
        $options = array(
            'conditions' => array('deleted is NULL and login=?', $email)
        );

        return self::find($options);
    }


############################################################################################################################################    

    static function forgotPassword($email, $send_email = true)
    {
        if (!self::exists($email))
        {
            return false;
        }

        $options = array(
            'conditions' => array('deleted is null 
                and login = ?', $email)
        );

        $newPassword = self::generatePassword(8);

        $user = self::find($options);

        if ($user)
        {
            if ($send_email)
            {
                \Needsandfun\Controller\Email::get()->confirmFlushPassword($user);    
            }
        }
        return $user;
    }

    public function updateNewPassword($password = false)
    {
        if (!$password) 
        {
            $password = self::generatePassword(8);
        }

        $this->new_password = self::password($password);
        $this->save();

        return $password;
    }

############################################################################################################################################    

    static function register($login, $password = false, $email = true)
    {
        if (!$password)
        {
            $password = self::generatePassword(8);
        }

        if (Email::validate($login))
        {
            if (!self::exists($login))
            {
                $user = new self();
                $user->login    = $login;
                $user->password = self::password($password);
                $user->save();

                \Core\Model\Client::createProfile($user);
                \Needsandfun\Controller\Email::get()->userAdded($user, array('password' => $password));

                return $user;
            }
            else
            {
                \Core\Page::get()->setMessage('Пользователь с таким имейлом уже существует!');
                return false;
            }
        }
        else
        {
            \Core\Page::get()->setMessage('Недопустимый адрес электронной почты!');
        }

        return false;        
    }

############################################################################################################################################    
    

    static function activateUser($key)
    {
        $options = array(
            'conditions' => array('md5(concat(id, login, password)) = ? and blocked = 1', $key),
        );

        $user = self::find($options);

        if ($user)
        {
            $user->blocked = 0;
            $user->save();
        }

        return $user;
    }    

    public function getActivateKey()
    {
        $key = md5($this->id . $this->login . $this->password);
        return $key;
    }    

############################################################################################################################################


    static function confirmFlushPassword($key)
    {
        $options = array(
            'conditions' => array('md5(concat(id, login, new_password)) = ?', $key),
        );

        $user = self::find($options);

        if ($user)
        {
            $user->password = $user->new_password;
            $user->save();
        }

        return $user;
    }    

    public function getConfirmKey()
    {
        $key = md5($this->id . $this->login . $this->new_password);
        return $key;
    }      

############################################################################################################################################

    public function updateProfile($values)
    {
        $client = $this->client;

        if (!empty($values['first_name']))
        {
            $client->first_name = $values['first_name'];
        }

        if (!empty($values['last_name']))
        {
            $client->last_name = $values['last_name'];
        }

        if (!empty($values['phone']))
        {
            $client->phone = $values['phone'];
        }

        \Core\Page::get()->setMessage('Ваши данные обновлены успешно!');

        $client->save();
    }

    static function generatePassword($number = 8)
    {
        $password = '';
        
        for ($i=0; $i < $number; $i++)
        {
            $password .= round(rand(0, 1))
                    ? strtolower(chr(rand(65, 90)))
                    : chr(rand(65, 90));
        }
        
        return $password;
    }    
   
    public function set_plain_password($password)
    {
        return $this->password = self::password($password);
    }
    
    public function get_fio()
    {
        $fio = array();
        
        if (!empty($this->firstname))
        {
            array_push($fio, $this->firstname);
        }
        
        if (!empty($this->lastname))
        {
            array_push($fio, $this->lastname);
        }
        
        return implode(' ', $fio);
    }
    
    static public function password($password)
    {
        return md5($password);
    }    

############################################################################################################################################

    /**
     * авторизация пользователя в системе
     * проставляем секретный ключ
     * 
     * @todo необходимо сделать более серьезную защиту токена, зависящего 
     * от браузера и текущего айпи
     */
    static function auth($login, $password)
    {
        $message = false;
        $user = self::getUserByLoginPassword($login, $password);

        if ($user)
        {
            if (!$user->blocked)
            {
                $token = Usertoken::create($user)->token;
                \Core\Storage::init('storable')->setValue(AUTH_TOKEN, $token);    
            }
            else
            {
                $message = 'Вы не подтвердили свой аккаунт. Письмо с инструкцией по активации аккаунта отправлено на ваш email!';
                \Needsandfun\Controller\Email::get()->activateAccount($user);
            }
        }
        else
        {
            $message = 'Имя пользователя и пароль, которые вы ввели — не верны!';
        }

        if ($message)
        {
            \Core\Page::get()->setMessage($message);    
        }
        
    }

    static function getUserByLoginPassword($login, $password)
    {
        $options = array(
            'conditions' => array('deleted is null 
                and login = ? 
                and password = md5(?)', $login, $password)
        );
        return self::find($options);
    }

    public function checkAuth($token)
    {
        $token = Usertoken::getToken($token);
        $user = $token 
            ? $token->user
            : false;

        return $user;
    }
}