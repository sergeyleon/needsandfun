<?php

namespace Managers\Controller;

class Places extends \Core\Abstracts\Singleton
{
    public function __construct() 
    {
        if (isset($_POST['savePlace']))
        {
            $this->_savePlace($_POST);
        }
        else if (isset($_POST['saveCategory'])) 
        {
            $this->_saveCategory($_POST);
        }
        else 
        {
            Index::drawMenu();
        }
    }
    
    public function index() 
    {
        $this->page['items'] = \Core\Model\Placecategory::all(array('conditions' => 'deleted is null', 'order' => 'name'));
        $this->page->display('places/index.twig');
    }
    
    public function add()
    {
        $this->_form();
    }

    public function edit($placeId)
    {
        $this->page['item'] = \Core\Model\Place::find($placeId);
        
        $this->_form();
    }
    
    public function forbidPlace($placeId)
    {
        $place = \Core\Model\Place::find($placeId);
        $place->is_checked = false;
        $place->save();
        
        $this->router->go($this->router->generate('manage_places_index'));        
    }

    public function confirmPlace($placeId)
    {
        $place = \Core\Model\Place::find($placeId);
        $place->is_checked = true;
        $place->save();
        
        $this->router->go($this->router->generate('manage_places_index'));
    }
    
    public function _savePlace($values)
    {
        $place = empty($values['id'])
            ? new \Core\Model\Place()
            : \Core\Model\Place::find($values['id']);
            
        if ($values['metro']) $place->metro_id = $values['metro'];
        
        $place->is_checked  = $values['moderation'];
        
        $place->category_id = $values['category'];
        $place->address     = $values['address'];
        $place->announce    = $values['announce'];
        $place->name        = $values['name'];        
        $place->description = $values['description'];        
        
        $place->maps_yandex = $values['maps_yandex'];        
        $place->maps_google = $values['maps_google'];  
              
        $place->phone       = $values['phone'];
        $place->email       = $values['email'];        
        $place->www         = $values['www'];                        
        
        $place->save();
        
        $itempicture = new \Core\Model\Placepicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $place, $itempicture);
        
        $this->router->go($this->router->generate('manage_places_index'));
    }
       
    public function _form()
    {
        $this->page['categories'] = \Core\Model\Placecategory::all(array('conditions' => 'deleted is null', 'order' => 'name'));    
        $this->page['metrolines'] = \Core\Model\Metroline::all(array('conditions' => 'deleted is null', 'order' => 'name'));
        
        $this->page->display('places/form.twig');
    }
    
    public function remove($placeId)
    {
        $place = \Core\Model\Place::find($placeId);
        $place->deleted = new \DateTime();
        $place->save();
        $this->router->go($this->router->generate('manage_places_index'));    
    }
    
    public function categoryAdd()
    {
        $this->_formCategory();
    }

    public function categoryEdit($categoryId)
    {
        $this->page['item'] = \Core\Model\Placecategory::find($categoryId);
        $this->_formCategory();
    }    
    
    public function categoryRemove($categoryId)
    {
        $category = \Core\Model\Placecategory::find($categoryId)->find($categoryId);
        $category->deleted = new \DateTime();
        $category->save();

        $this->router->go($this->router->generate('manage_places_index'));
    }    
    
    
    public function _formCategory()
    {
        $this->page->display('places/categories/form.twig');
    }
    
    public function _saveCategory($values)
    {
        $category = empty($values['id'])
            ? new \Core\Model\Placecategory()
            : \Core\Model\Placecategory::find($values['id']);
            
        $category->name = $values['name'];
        $category->save();
        
        $this->router->go($this->router->generate('manage_places_index'));
        
    }
    
}