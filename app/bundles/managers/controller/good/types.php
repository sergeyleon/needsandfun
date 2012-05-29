<?php

namespace Managers\Controller;

class GoodTypes extends \Core\Abstracts\Singleton
{
    protected function __construct()
    {
        if (isset($_POST['proceed']))
        {
            $this->_proceed($_POST);
        }
        else 
        {
            Index::drawMenu();
        }
    }
    
    public function index()
    {
        $page = $this->getPage();

        $page['items'] = \Core\Model\Type::all(array('order' => 'name'));
        $page->display('goods/types/index.twig');
    }
    
    public function add() 
    {
        $this->_form();
    }
    
    public function edit($itemId) 
    {
        $page = $this->getPage();
        
        $page['item'] = \Core\Model\Type::find($itemId);
        $this->_form();        
    }
    
    public function remove($itemId) 
    {
        $type = \Core\Model\Type::find($itemId);
        foreach ($type->typeproperties as $typeproperty) 
        {
            $typeproperty->delete();
        }
        
        $type->delete();
        
        $this->_gotoIndex();
    }
    
    private function _form()
    {
        $page = $this->getPage();
        
        $page['goodProperties'] = \Core\Model\Property::all(array('order' => 'name'));
        $page['propertyTypes'] = \Core\Model\Propertytype::all();
        $page->display('goods/types/form.twig');
    }
    
    private function _proceed($values)
    {
        $action = empty($values['id'])
            ? 'add'
            : 'edit';
            
        $type = 'add' == $action
            ? new \Core\Model\Type()
            : \Core\Model\Type::find($values['id']);
        
        $type->name        = $values['name'];
        $type->description = $values['description'];
        $type->save();
        
        if ('edit' == $action)
        {
            $_typeProperties = \Core\Model\Typeproperty::all(array('conditions' => array('type_id = ?', $type->id)));
            
            $typeProperties = array();
            foreach ($_typeProperties as $typeproperty)
            {
                $typeProperties[] = $typeproperty->id;
            }
        }
        
        
        $ids = array();
        $weight = 0;
        
        foreach ($_POST['properties'] as $_property) 
        {
            $property = false;    
            if (empty($_property['property_id']) && !empty($_property['name']))
            {
                $property = new \Core\Model\Property();
                $property->property_type_id = $_property['type'];                
                $property->save();
            }
            else if (!empty($_property['property_id'])) 
            {
                $property = \Core\Model\Property::find($_property['property_id']);
            }
            
            $updatedProperties = array();
            
            if ($property)
            {
                $property->name             = $_property['name'];
                $property->property_type_id = $_property['type'];
                $property->save();
                
                $typeproperty = empty($_property['typeproperty_id'])
                    ? new \Core\Model\Typeproperty()
                    : \Core\Model\Typeproperty::find($_property['typeproperty_id']);
                    
                
                if (in_array($typeproperty->id, $typeProperties))
                {
                    unset($typeProperties[array_search($typeproperty->id, $typeProperties)]);
                }
                    
                $typeproperty->property_id = $property->id;
                $typeproperty->type_id     = $type->id;
                $typeproperty->weight      = $weight++;                
                $typeproperty->save();
                
                $propertyType = \Core\Model\Propertytype::find($_property['type']);
                
                switch ($propertyType->type)
                {
                    case 'array':
                    case 'range':
                        $newValues = explode(',', $_property['value']);
                        
                        foreach ($newValues as &$newValue)
                        {
                            $newValue = trim($newValue);
                        }
                        break;
                    default:
                        $newValues = array($_property['value']);
                        break;
                }
                
                $values = \Core\Model\Propertyvalue::all(array('conditions' => array('property_id = ?', $property->id)));
                
                $i = 0;
                foreach ($values as $value)
                {   
                    if (isset($newValues[$i]))
                    {
                        
                        $value->value = $newValues[$i];
                        $value->save();
                        unset($newValues[$i]);
                    }
                    else {
                        $value->delete();    
                    }
                    $i++;                    
                }
                
                if (count($newValues))
                {
                    foreach ($newValues as $newValue) 
                    {
                        $_value = new \Core\Model\Propertyvalue();
                        $_value->value = $newValue;
                        $_value->property_id = $property->id;
                        $_value->save();
                    }
                }
                
                $updatedProperties[] = $property->id;
            }
        }
        
        if (count($typeProperties))
        {
            $toDelete = \Core\Model\Typeproperty::all(array('conditions' => array('id in (' . implode(', ', $typeProperties) . ')')));
            foreach ($toDelete as $delete) 
            {
                $delete->delete();
            }
        }
        
        $this->_gotoIndex();
    }
    
    private function _gotoIndex()
    {
        $router = $this->getRouter();
        $router->go($router->generate('manage_goods_types'));    
    }
}
