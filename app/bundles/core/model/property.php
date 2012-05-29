<?php 

namespace Core\Model;

class Property extends \ActiveRecord\Model
{
    static $has_many = array(
        array('typeproperties'),
        array('propertyvalues')        
    );
    
    static $belongs_to = array(
//        array('goodproperty', 'foreign_key' => 'property_id'),        
        array('propertytype', 'foreign_key' => 'property_type_id')
    );
    
    public function getValues()
    {        
        switch ($this->propertytype->type)
        {
            case 'array':
            case 'range':
                $values = array();
                foreach ($this->propertyvalues as $value)
                {
                    array_push($values, $value->value);
                }
                $values = implode(', ', $values);
                break;
            default:
                $values = $this->propertyvalues[0]->value;
                break;
        }
        return $values;
    }
}