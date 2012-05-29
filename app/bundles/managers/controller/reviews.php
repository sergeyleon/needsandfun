<?php

namespace Managers\Controller;

class Reviews extends \Core\Abstracts\Singleton
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
    
    public function confirm($reviewId)
    {
        \Core\Model\Review::find($reviewId)->confirm();
        $this->router->go($this->router->generate('manage_reviews_index'));
    }

    public function edit($reviewId)
    {
        $this->page['item'] = \Core\Model\Review::find($reviewId);
        $this->_form();
    }
    
    public function remove($reviewId)
    {
        \Core\Model\Review::find($reviewId)->remove();
        $this->router->go($this->router->generate('manage_reviews_index'));
    }
    
    public function index()
    {
        $this->page['items'] = \Core\Model\Review::all(array(
            'order' => 'is_checked'
        ));
        $this->page->display('reviews/index.twig');
    }
    
    private function _form()
    {
        $this->page->display('reviews/form.twig');
    }
    
    private function _proceed($values)
    {
        $review = \Core\Model\Review::find($values['id']);
        
        $review->is_checked  = $values['checked'];
        $review->save();

        $this->router->go($this->router->generate('manage_reviews_index'));
    }
}