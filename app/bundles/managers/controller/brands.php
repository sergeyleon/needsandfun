<?php

namespace Managers\Controller;

class Brands extends \Core\Abstracts\Singleton
{
    public function __construct()
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
    
    public function add()
    {
        $this->_form();
    }

    public function edit($brandId)
    {
        $this->page['brand'] = \Core\Model\Brand::find($brandId);
        $this->_form();
    }
    
    public function remove($brandId)
    {
        \Core\Model\Brand::find($brandId)->delete();
        $this->router->go($this->router->generate('manage_brands_index'));        
    }
    
    public function index()
    {
        $this->page['brands'] = \Core\Model\Brand::all(array('order' => 'name'));
        $this->page->display('brands/index.twig');
    }
    
    private function _form()
    {
        $this->page->display('brands/form.twig');
    }
    
    private function _proceed($values)
    {
        $brand = empty($values['id'])
            ? new \Core\Model\Brand()
            : \Core\Model\Brand::find($values['id']);
        
        $brand->name        = $values['name'];
        $brand->description = $values['description'];
        $brand->save();
        
        $picture = new \Core\Model\Brandpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $brand, $picture);
        
        $this->router->go($this->router->generate('manage_brands_index'));
    }
}