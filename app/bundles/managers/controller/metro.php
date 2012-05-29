<?php

namespace Managers\Controller;

class Metro extends \Core\Abstracts\Singleton
{
    public function __construct()
    {
        if (isset($_POST['saveLine']))
        {
            $this->_saveLine($_POST);        
        }
        else if (isset($_POST['saveMetro']))
        {
            $this->_saveMetro($_POST);
        }
        else {
            Index::drawMenu();            
        }
    }
    
    public function index()
    {   
        $options = array(
            'select' => 'metro_lines.*',
            'joins' => 'left join metros on (metros.metroline_id = metro_lines.id)',
            'conditions' => 'metro_lines.deleted is null', 
            'order' => 'count(metros.id), name',
            'group' => 'metros.metroline_id'
        );
        $this->page['lines'] = \Core\Model\Metroline::all($options);
        
        $this->page->display('metro/index.twig');
    }
    
    
    public function add()
    {
        $this->_form();
    }
    
    public function edit($metroId)
    {
        $this->page['item'] = \Core\Model\Metro::find($metroId);
        $this->_form();
    }    
    
    public function remove($metroId)
    {
        $line = \Core\Model\Metro::find($metroId);
        $line->deleted = new \DateTime();
        $line->save();
        $this->router->go($this->router->generate('manage_metro_index'));        
    }    
        
    private function _form()
    {
        $this->page['metrolines'] = \Core\Model\Metroline::all(array('conditions' => 'deleted is null', 'order' => 'name'));
        $this->page->display('metro/form.twig');
    }
    
    
    private function _saveMetro($values)
    {    
        $metro = !empty($values['id'])
            ? \Core\Model\Metro::find($values['id'])
            : new \Core\Model\Metro();
        
        $metro->name = $values['name'];
        $metro->metroline_id = $values['metroline'];
        $metro->save();
        
        $this->router->go($this->router->generate('manage_metro_index'));
//        $this->page->display('metro/line/form.twig');
    }    
    
    
    
    public function addLine()
    {
        $this->_formLine();
    }
    
    public function editLine($lineId)
    {
        $this->page['line'] = \Core\Model\Metroline::find($lineId);
        $this->_formLine();
    }    
    
    public function removeLine($lineId)
    {
        $line = \Core\Model\Metroline::find($lineId);
        $line->deleted = new \DateTime();
        $line->save();
        $this->router->go($this->router->generate('manage_metro_index'));        
    }        
        
    private function _formLine()
    {
        $this->page->display('metro/line/form.twig');
    }

    private function _saveLine($values)
    {    
        $line = !empty($values['id'])
            ? \Core\Model\Metroline::find($values['id'])
            : new \Core\Model\Metroline();
        
        $line->name = $values['name'];
        $line->color = $values['color'];
        $line->save();
        
        $this->router->go($this->router->generate('manage_metro_index'));
//        $this->page->display('metro/line/form.twig');
    }    
}