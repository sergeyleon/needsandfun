<?php

namespace Managers\Controller;

class Banners extends \Core\Abstracts\Singleton
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

    public function edit($bannerId)
    {
        $this->page['banner'] = \Core\Model\Banner::find($bannerId);
        $this->_form();
    }
    
    public function remove($bannerId)
    {
        \Core\Model\Banner::find($bannerId)->delete();
        $this->router->go($this->router->generate('manage_banners_index'));
    }
    
    public function index()
    {
        $this->page['uploadErrors'] = $this->flashStorage->getValue('uploadError');        
        $this->page['items'] = \Core\Model\Banner::getAll();
        $this->page->display('banners/index.twig');
    }
    
    private function _form()
    {
        $this->page['types'] = \Core\Model\Bannertype::getAll();
        $this->page->display('banners/form.twig');
    }
    
    private function _proceed($values)
    {
        $params = $this->router->getParams();
        $id = $params['bannerId'];

        $banner = empty($id)
            ? new \Core\Model\Banner()
            : \Core\Model\Banner::find($id);
        
        $banner->title   = $values['title'];
        $banner->type_id = $values['type'];
        $banner->link    = $values['link'];
        $banner->save();
        
        $picture = new \Core\Model\Bannerpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $banner, $picture);
        
        $this->router->go($this->router->generate('manage_banners_index'));
    }
}