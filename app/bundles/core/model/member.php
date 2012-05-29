<?php 

namespace Core\Model;

class Member extends \ActiveRecord\Model
{
	static $table = 'members';

    static $belongs_to = array(
        array('client'),
        array('event')        
    );

    static function getAll($_options) 
    {
    	$options = array();

    	if (!empty($_options['event']))
    	{
    		$options['conditions'] = array(
    			'event_id = ?', $_options['event']
    		);
    	}

    	if (!empty($_options['client']))
    	{
    		if (isset($options['conditions']))
    		{
    			$options['conditions'][0] .= ' and client_id = ?';
    		}
    		else
    		{
    			$options['conditions'] = array('client_id = ?');
    		}

    		array_push($options['conditions'], $_options['client']);
    	}

    	return self::all($options);
    }
}
