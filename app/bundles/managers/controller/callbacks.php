<?php

namespace Managers\Controller;

class Callbacks extends \Core\Abstracts\Singleton
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
    
    public function edit($callbackId)
    {
        $this->page['item'] = \Core\Model\Callback::find($callbackId);
        $this->_form();
    }
    
    public function remove($callbackId)
    {
        \Core\Model\Callback::find($callbackId)->delete();
        $this->router->go($this->router->generate('manage_callbacks_index'));        
    }
    
    public function index()
    {
        $this->page['items'] = \Core\Model\Callback::getAll();
        $this->page->display('callbacks/index.twig');
    }
    
    private function _form()
    {
        $this->page->display('callbacks/form.twig');
    }
    
    private function _proceed($values)
    {
        $item = empty($values['id'])
            ? new \Core\Model\Callback()
            : \Core\Model\Callback::find($values['id']);

        $now = new \DateTime();

        $item->comment     = $values['comment'];
        $item->is_called = $values['is_called'] 
            ? $now
            : null;
        
        $item->save();        
        $this->router->go($this->router->generate('manage_callbacks_index'));
    }
}