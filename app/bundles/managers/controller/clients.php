<?php

namespace Managers\Controller;

class Clients extends \Core\Abstracts\Singleton
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

    public function index()
    {
        $this->page['items']    = \Core\Model\Client::getAll();
        $this->page->display('clients/index.twig');
    }
    
    public function add()
    {
        $this->_form();
    }

    public function edit($clientId)
    {
        $this->page['item'] = \Core\Model\Client::find($clientId);
        $this->_form();
    }
    
    public function remove($clientId)
    {
        $client = \Core\Model\Client::find($clientId);
        $client->deleted = new \DateTime();
        $client->save();

        $this->router->go($this->router->generate('manage_clients_index'));        
    }
    
    
    private function _form()
    {
        $this->page->display('clients/form.twig');
    }
    
    private function _proceed($values)
    {
        if (empty($values['id']))
        {
            $user = \Core\Model\User::register($values['email']);
            $client = $user->client;
        }
        else
        {
            $client = \Core\Model\Client::find($values['id']);
        }

        $client->first_name  = $values['first_name'];
        $client->last_name   = $values['last_name'];
        $client->phone       = $values['phone'];
        $client->description = $values['description'];
        
        $client->save();

        $picture = new \Core\Model\Clientpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $client, $picture);
        
        $this->router->go($this->router->generate('manage_clients_index'));
    }
}