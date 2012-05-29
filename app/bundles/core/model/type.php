<?php 

namespace Core\Model;

class Type extends \ActiveRecord\Model
{
    static $has_many = array(
        array('goods'),    
        array('typeproperties', 'order' => 'weight'),
        array('properties', 'through' => 'typeproperties', 'order' => 'type_properties.weight')
    );
    
    static function getProperties($typeId) 
    {
        $type = Type::find($typeId);
        $properties = array();
        
        foreach ($type->properties as $property) 
        {
            $item = array(
                'id'   => $property->id,
                'name' => $property->name
            );
            
            array_push($properties, $item);
        }
        
        return $properties;
    } 
    
}