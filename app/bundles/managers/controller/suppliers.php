<?php

namespace Managers\Controller;

class Suppliers extends \Core\Abstracts\Singleton
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

    public function edit($supplierId)
    {
        $this->page['supplier'] = \Core\Model\Supplier::find($supplierId);
        $this->_form();
    }
    
    public function remove($supplierId)
    {
        \Core\Model\Supplier::find($supplierId)->delete();
        $this->router->go($this->router->generate('manage_suppliers_index'));        
    }
    
    public function index()
    {
    
      if(isset($_GET['supplier'])) {
        $this->page['goods'] = \Core\Model\Good::all(array('conditions' => array('supplier_id = ? ', $_GET['supplier'])));
        $this->page->display('suppliers/goods.twig');
      }
      else {
        $this->page['suppliers'] = \Core\Model\Supplier::all(array('order' => 'name'));
        $this->page->display('suppliers/index.twig');
      }
    }
    
    private function _form()
    {
        $this->page->display('suppliers/form.twig');
    }
    
    private function _proceed($values)
    {
        $brand = empty($values['id'])
            ? new \Core\Model\Supplier()
            : \Core\Model\Supplier::find($values['id']);
        
        $brand->name        = $values['name'];
        $brand->description = $values['description'];
        $brand->save();
        
       // $picture = new \Core\Model\Brandpicture();
      //  \Core\Model\Picture::multipleUpload($_FILES['pictures'], $brand, $picture);
        
        $this->router->go($this->router->generate('manage_suppliers_index'));
    }
}