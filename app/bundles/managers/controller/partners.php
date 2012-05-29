<?php

namespace Managers\Controller;

class Partners extends \Core\Abstracts\Singleton
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

    public function edit($partnerId)
    {
        $this->page['partner'] = \Core\Model\Partner::find($partnerId);
        $this->_form();
    }
    
    public function remove($partnerId)
    {
        \Core\Model\Partner::find($partnerId)->delete();
        $this->router->go($this->router->generate('manage_partners_index'));        
    }
    
    public function index()
    {
        $this->page['partners'] = \Core\Model\Partner::all(array('order' => 'name'));
        $this->page->display('partners/index.twig');
    }
    
    private function _form()
    {
        $this->page->display('partners/form.twig');
    }
    
    private function _proceed($values)
    {
        $partner = empty($values['id'])
            ? new \Core\Model\Partner()
            : \Core\Model\Partner::find($values['id']);
        
        $partner->name        = $values['name'];
        $partner->description = $values['description'];
        $partner->save();
        
        $picture = new \Core\Model\Partnerpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $partner, $picture);
        
        $this->router->go($this->router->generate('manage_partners_index'));
    }
}