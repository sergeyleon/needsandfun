<?php

namespace Managers\Controller;

class Goods extends \Core\Abstracts\Singleton
{
    protected function __construct()
    {
        if (isset($_POST['proceed']))
        {
            $this->_proceed($_POST);
        }
        else if (isset($_POST['proceed_category']))
        {
            $this->_saveCategory($_POST);
        }        
        else {
            Index::drawMenu();
            $page = $this->getPage();    
            
            $page['brands'] = \Core\Model\Brand::all();
            $page['types']  = \Core\Model\Type::all();        
        }
    }
    
    public function _proceed($values)
    {
        $good = empty($values['id'])
            ? new \Core\Model\Good()
            : \Core\Model\Good::find($values['id']);

        $good->name             = $values['name'];
        $good->brand_id         = $values['brand'];
        $good->article          = $values['article'];        
        $good->type_id          = $values['type'];
        $good->age_from         = $values['age_from'];
        $good->age_to           = $values['age_to'];
        $good->sex              = $values['sex'];
        $good->discount         = $values['discount'];
        $good->compound         = $values['compound'];
        $good->description      = $values['description'];
        $good->meta_keywords    = $values['meta_keywords'];
        $good->meta_description = $values['meta_description'];
        $good->save();

        $goodpicture = new \Core\Model\Goodpicture();
        \Core\Model\Picture::multipleUpload($_FILES['pictures'], $good, $goodpicture);
        
        $properties = \Core\Model\Goodproperty::all();
        
        foreach ($properties as $property)
        {
            $property->delete();
        }
        
        foreach ($values['property'] as $id => $_property)
        {
            $goodproperty = new \Core\Model\Goodproperty();
            $propertyType = \Core\Model\Property::find($id)->propertytype->type;
            
            $goodproperty->good_id     = $good->id;
            $goodproperty->property_id = $id;            
            
            $goodproperty->value = $_property;
            $goodproperty->save();
        }
        
        
        if (isset($values['sizes']))
        {
            $toDelete = array();
            
            foreach ($values['sizes'] as $_size)
            {
                if (!empty($_size['price']))
                {
                    $size = empty($_size['id'])
                        ? new \Core\Model\Size()
                        : \Core\Model\Size::find($_size['id']);
                        
                    $size->name = $_size['name'];
                    $size->price = $_size['price'];
                    $size->good_id = $good->id;
                    
                    $size->save();
                    $dontDelete[] = $size->id;
                }
            }
            
            $deleteSizes = \Core\Model\Size::all(array('conditions' => array('good_id = ?', $good->id)));
            
            foreach ($deleteSizes as $size) 
            {
                if (!in_array($size->id, $dontDelete))
                {
                    $size->deleted = new \DateTime();
                    $size->save();
                }
            }
        }

        $this->router->go($this->router->generate('manage_goods_index'));
    }
    
    public function action() 
    {        
        switch ($_POST['action'])
        {
            case 'addCategory':
                $this->_addCategory($_POST['goodId'], $_POST['categoryId']);
                break;

            case 'newGood':
                $this->_isNewGood($_POST['goodId'], true);
                break;                

            case 'notNewGood':
                $this->_isNewGood($_POST['goodId']);
                break;                                
                
            case 'removeCategory':
                $this->_removeCategory($_POST['categoryId']);
                break;
                
            case 'deleteGood':
                $this->_deleteGood($_POST['goodId']);
                break;
                
            case 'deleteFromCategory':
                $this->_deleteFromCategory($_POST['goodId'], $_POST['categoryId']);
                break;
                
            case 'show':
                \Core\Model\Good::find($_POST['goodId'])->show();
                break; 
                
            case 'hide':
                \Core\Model\Good::find($_POST['goodId'])->hide();
                break;
        }
    }

    private function _isNewGood($good_id, $value = false)
    {
        $good = \Core\Model\Good::find($good_id);
        $good->is_new = $value;
        $good->save();        
    }
    
    private function _deleteGood($goodId) 
    {
        $good = \Core\Model\Good::find($goodId);
        $good->deleted = new \DateTime();       
        $good->save();        
    }
    
    private function _addCategory($goodId, $categoryId)
    {
        $items = \Core\Model\Goodcategory::all(array('conditions' => array('good_id = ? and category_id=?', $goodId, $categoryId)));
        
        if (!count($items))
        {       
            $goodCategory = new \Core\Model\Goodcategory();
            $goodCategory->good_id = $goodId;
            $goodCategory->category_id = $categoryId;
            $goodCategory->save();
        }
    }
    
    private function _removeCategory($categoryId)
    {
        $category = \Core\Model\Category::find($categoryId);
        $category->deleted = new \DateTime();
        $category->save();        
    }    
    
    private function _deleteFromCategory($goodId, $categoryId)
    {
        $items = \Core\Model\Goodcategory::all(array('conditions' => array('good_id = ? and category_id=?', $goodId, $categoryId)));
        
        foreach ($items as $item)
        {
            $item->delete();
        }
    }
    
    public function index()
    {
        $this->page['selectedCategory'] = $this->cookieStorage->getValue('goods_selectedCategory') ?: 'all';

        $this->page['categories'] = \Core\Model\Category::getAll();
        $this->page['goods'] = \Core\Model\Good::all(array('conditions' => array('deleted is null')));
        
        $this->page->display('goods/index.twig');
    }
    
    public function addCategory()
    {
        $category = new \Core\Model\Category();
        $category->name = $_POST['categoryName'];
        $category->save();
        
        $this->router->go($this->router->generate('manage_goods_index'));
    }    

    public function categoryWeight()
    {
        $weight = count($_POST);
        foreach($_POST as $id => $value)
        {
            $category = \Core\Model\Category::find($id);
            
            $category->parent_id = isset($value['parentId']) 
                ? $value['parentId']
                : null;
                
            $category->weight = $weight - ($value['index'] + 1);
            $category->save();
        }   
    }
    
    public function categoryVisibility()
    {
        $categoryId = $_POST['categoryId'];
        
        $func = isset($_POST['visible']) && $_POST['visible']
            ? 'show'
            : 'hide';
            
        $category = \Core\Model\Category::find($categoryId)->$func();
    }
    
    
    public function removeCategory() 
    {  
        $category = \Core\Model\Category::find($_POST['deleteCategory']);
        
        foreach ($category->categories as $item)
        {
            $item->delete();
        }
        $category->delete();
    }
    
    public function editCategory($categoryId)
    {
        $this->page['item']       = \Core\Model\Category::find($categoryId);
        $this->page['categories'] = \Core\Model\Category::all(array('conditions' => array('parent_id is null and NOT(id = ?)', $categoryId), 'order' => 'name'));
        $this->page->display('goods/category.twig');
    }
    
    private function _saveCategory($values)
    {
        $category = isset($values['id'])
            ? \Core\Model\Category::find($values['id'])
            : new \Core\Model\Category();
            
        $category->name       = $values['name'];
        $category->is_visible = $values['visible'];

        $category->description      = $values['description'];
        $category->meta_description = $values['meta_description'];
        $category->meta_keywords    = $values['meta_keywords'];

        $category->parent_id  = 'root' != $values['parent'] 
            ? $values['parent']
            : null;

        $category->save();
        
        $this->router->go($this->router->generate('manage_goods_index'));
    }
    
    public function add()
    {
        $this->_form();
    }
    
    public function edit($goodId)
    {
        $page = $this->getPage();
        $page['item'] = \Core\Model\Good::find($goodId);
        $page['properties'] = $page['item']->getProperties();
        
        $this->_form();
    }
    
    private function _form()
    {
        $page = $this->getPage();
        $page->display('goods/form.twig');        
    }    
}
