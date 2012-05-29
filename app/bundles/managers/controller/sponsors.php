<?php

namespace Managers\Controller;

class Sponsors extends \Core\Abstracts\Singleton
{
    protected function __construct()
    {
        if (isset($_POST['proceed']))
        {
            $this->_proceed($_POST);
        }
        else {
            Index::drawMenu();
            $page = $this->getPage();    
        }
    }
    
    public function _proceed($values)
    {
        $sponsor = empty($values['id'])
            ? new \Core\Model\Sponsor()
            : \Core\Model\Sponsor::find($values['id']);
            
        $sponsor->name = $values['name'];
        $sponsor->link = $values['link'];
        $sponsor->description = $values['description'];
        
        $sponsor->save();
        
        $itempicture = new \Core\Model\Sponsorpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $sponsor, $itempicture);        
        
        $this->router->go($this->router->generate('manage_sponsors_index'));
    }
    
    public function index()
    {
        $this->page['sponsors'] = \Core\Model\Sponsor::getAll();
        
        $this->page->display('sponsors/index.twig');
    }
    
    public function add()
    {
        $this->_form();
    }    

    public function edit($sponsorId)
    {
        $this->page['sponsor'] = \Core\Model\Sponsor::find($sponsorId);
        $this->_form();
    }    
    
    public function remove($sponsorId)
    {
        $sponsor = \Core\Model\Sponsor::find($sponsorId);
        $sponsor->deleted = new \DateTime();
        $sponsor->save();

        $this->router->go($this->router->generate('manage_sponsors_index'));        
    }    

    public function _form()
    {
        $this->page->display('sponsors/form.twig');        
    }
}
