<?php

/**
 * Description of UserTokens
 */
namespace Core\Model;

class Usertoken extends \ActiveRecord\Model
{
    static $table = 'tokens';
    static $belongs_to = array(
        array('user')
    );
    
    /**
     * Метод-сеттер авторизационного ключа
     * Преобразует указанное значение в хеш
     */
    public function set_token($userId)
    {
        $token = md5($userId . md5(AUTH_HASH) . time());
        $this->assign_attribute('token', $token);
    }    

    public function flush()
    {
        $this->delete();
    }

    static function getToken($token)
    {
        return self::find_by_token($token) ?: false;
    }

    static function create(\Core\Model\User $user)
    {
        $host = \Core\Router::get()->getHost();
        
        $token = new self();
        $token->user_id = $user->id;
        $token->token = md5($host . $token->user_id);
        $token->save();
        
        return $token;
    }
}