<?php

namespace Managers\Controller;

   //////////////////////
              /* Создание preview изображения */
function CreateThumb($file,$maxwdt,$maxhgt, $dest, $crop) 
{
	// проверяет типы файлов
	list($owdt,$ohgt,$otype)=getimagesize($file);
	// $owdt - оригинальная ширина
	// $owdt - оригинальная ширина
	// $otype - оригинальная тип файла
	switch($otype) 
	{
		case 1:  
			$newimg=imagecreatefromgif($file); 
		break;
		case 2:  
			$newimg=imagecreatefromjpeg($file); 
		break;
		case 3:  
			$newimg=imagecreatefrompng($file); 
		break;
		default: 
			echo "Unkown filetype (file $file, typ $otype)"; 
		return;
	}
   
	if($newimg) 
	{
		Resample($newimg, $owdt, $ohgt, $maxwdt, $maxhgt, $crop);
		   
		if(!$dest) 
		return $newimg;
		   
		if(!is_dir(dirname($dest))) //если нет директории
		mkdir(dirname($dest)); // создает директорию
	   
		switch($otype) 
		{
			case 1: 
				imagegif($newimg,$dest); 
			break;    
			case 2: 
				imagejpeg($newimg,$dest,95); 
			break;
			case 3: 
				imagepng($newimg,$dest);  
			break;
		}
		   
		imagedestroy($newimg);
	   
		chmod($dest,0644);
	}
}

function Resample(&$img, $owdt, $ohgt, $maxwdt, $maxhgt, $crop) 
{
	// если выбрали crop по высоте
	if($crop == '2') {
		$res = $maxwdt;
		
		$newwdt = $res;
		$newhgt = $res;
		
		$dest = imagecreatetruecolor($res,$res);
		
		// вырезаем квадратную серединку по x, если фото горизонтальное 
		if ($owdt > $ohgt) {
			imagecopyresampled($dest, $img, 0, 0, round((max($owdt,$ohgt)-min($owdt,$ohgt))/2), 0, $res, $res, 
				min($owdt,$ohgt), 
				min($owdt,$ohgt)
			);
		}
		
		// вырезаем квадратную серединку по y, если фото вертикальное
		if ($owdt < $ohgt) {
			imagecopyresampled($dest, $img, 0, 0, 0, round((max($owdt,$ohgt)-min($owdt,$ohgt))/2), $res, $res,
				min($owdt,$ohgt), 
				min($owdt,$ohgt)
			); 
		}
		
		// квадратная картинка масштабируется без вырезок 
		if ($owdt == $ohgt) {
			imagecopyresampled($dest, $img, 0, 0, 0, 0, min($owdt,$ohgt), min($owdt,$ohgt), $owdt, $ohgt);
		}
		
		imagedestroy($img);
	   
		$img = $dest;
		return array($newwdt, $newhgt);	
	}
	
	// если выбрали crop по ширине
	if($crop == '1') {
		$res = $maxhgt;
		
		$newwdt = $res;
		$newhgt = $res;
		
		$dest = imagecreatetruecolor($res,$res);
		
		// вырезаем квадратную серединку по x, если фото горизонтальное 
		if ($owdt > $ohgt) {
			imagecopyresampled($dest, $img, 0, 0, round((max($owdt,$ohgt)-min($owdt,$ohgt))/2), 0, $res, $res, 
				min($owdt,$ohgt), 
				min($owdt,$ohgt)
			);
		}
		
		// вырезаем квадратную серединку по y, если фото вертикальное
		if ($owdt < $ohgt) {
			imagecopyresampled($dest, $img, 0, 0, 0, round((max($owdt,$ohgt)-min($owdt,$ohgt))/2), $res, $res,
				min($owdt,$ohgt), 
				min($owdt,$ohgt)
			); 
		}
		
		// квадратная картинка масштабируется без вырезок 
		if ($owdt == $ohgt) {
			imagecopyresampled($dest, $img, 0, 0, 0, 0, min($owdt,$ohgt), min($owdt,$ohgt), $owdt, $ohgt);
		}
		
		imagedestroy($img);
	   
		$img = $dest;
		return array($newwdt, $newhgt);
	}
	
	
	//------- Проверка отношений сторон
	
	if($crop == '0') {
		
		if($maxwdt > $owdt) { $maxwdt = $owdt;}
		if($maxhgt > $ohgt) { $maxhgt = $ohgt;}
		
		// если не задана ширина
		if(!$maxwdt) {
			$divwdt = 0;
		}
		else { 
			$divwdt = max(1,$owdt/$maxwdt);
			if($owdt == $ohgt) {
        $divwdt = min(1,$owdt/$maxwdt);
      }
		}
		// если не задана высота
		if(!$maxhgt) {
			$divhgt = 0;
		}
		else {
			$divhgt = max(1,$ohgt/$maxhgt);
			if($owdt == $ohgt) {
        $divwdt = min(1,$ohgt/$maxhgt);
      }
		}
		
		
		if($divwdt >= $divhgt)
		{
			$newwdt=$maxwdt;
			$newhgt=round($ohgt/$divwdt);
		} 
		else 
		{
			$newhgt=$maxhgt;
			$newwdt=round($owdt/$divhgt);
		}
		
		//------- Проверка отношений сторон
	
		$dest = imagecreatetruecolor($newwdt,$newhgt);
	
		imagecopyresampled($dest,$img,0,0,0,0,$newwdt,$newhgt,$owdt,$ohgt);  
		imagedestroy($img);
		   
		$img = $dest;
		return array($newwdt, $newhgt);
	}
}

class Goods extends \Core\Abstracts\Singleton
{
    protected function __construct()
    {
        
        if (isset($_POST['toexcel']))
        {
            $this->export($_POST); // Baltic IT adds
        }
        else if (isset($_POST['tomysql']))
        {
            $this->import($_POST); // Baltic IT adds
        }
        else if (isset($_POST['proceed']))
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
            $page['suppliers'] = \Core\Model\Supplier::all();
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
        $good->supplier_id      = $values['supplier'];
        $good->article          = $values['article'];
        $good->product_sku      = $values['product_sku'];
        $good->type_id          = $values['type'];
        $good->age_from         = $values['age_from'];
        $good->age_to           = $values['age_to'];
        $good->sex              = $values['sex'];
        $good->discount         = $values['discount'];
        $good->discount_date    = $values['discount_date'];
        $good->compound         = $values['compound'];
        $good->description      = $values['description'];
        $good->meta_keywords    = $values['meta_keywords'];
        $good->meta_description = $values['meta_description'];
        $good->p_price          = $values['p_price'];
        $good->in_stock         = $values['in_stock'];
        $good->sale             = $values['sale'];
        $good->sell_amount      = $values['sell_amount'];
        $good->link             = $values['link'];
        $good->title            = $values['title'];
        if(empty($values['title'])) { $good->title = $values['name']; }


        $checkitems = \Core\Model\Good::all(array('conditions' => array('link = ? and id != ?', $good->link, $values['id'] )));
        if (!empty($checkitems) ) {
          $page = \Core\Page::get();
          $message = "У товара есть совпадения! Измените ссылку"; 
          $page->setMessage($message);
        }
        
      

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
    
    
private function _getGoods($page = 1, $categories = false, $category = false)
    {
    
    
        if($category == "new") { $is_new = 'goods.is_new  = 1';} else { $is_new ='';} 
        $conditions = array(
            'goods.is_available = 1',
            'goods.deleted is null',
            $is_new
        );

        if ($categories)
        {
            $conditions[] = 'goods.id in (select good_id from goods_categories where category_id in (' . implode(', ', $categories) . '))';
        }
        

        $options = array('conditions' => array(implode(' AND ', $conditions)));
        
        if (!empty($variables))
        {
            $options['conditions'] = array_merge($options['conditions'], $variables);
        }

    //    $this->page['clearFilter'] = $this->router->generate($categories ? 'shop_category_page' : 'shop_index_page', array('page' => 'all', 'category' => $category));
        
        if (is_numeric($page))
        {
            $total = \Core\Model\Good::all($options);

            $options['limit']  = \Core\Model\Good::$perPage;
            $options['offset'] = \Core\Model\Good::$perPage * ($page - 1);

            $this->page['pager']   = \Core\Model\Good::getPager($page, $category, 'manage_goods_category_page', $total);
            
          //  var_dump($this->page['pager']);
        }
        
        $options['order']  = 'name';

        

        $goods = \Core\Model\Good::all($options);
        

        if (isset($total) && count($total) > 0 && count($goods) == 0 && $page > 1)
        {
            $this->router->go($this->router->generate('category_page', array('page' => 1)));
        }

        return $goods;
    }

    private function _categories($selected = false)
    {    
        $this->page['shopCategories'] = \Core\Model\Category::getAll($selected);
    }
    
    
    public function category_index($category, $page = 1) 
    {
    
        if($category != 'new') {
          $this->page['currentCategory'] = \Core\Model\Category::from_url($category);
          $category = $this->page['currentCategory']->encoded_key;
  
          $categoryId = $this->page['currentCategory']->id;
  
          $filters = array('categoryId' => $categoryId);
          $this->getStorage('flash')->setValue('categoryId', $categoryId);
          $this->_categories($categoryId);
              
          $categories = $this->page['currentCategory']->getChildren();
        }
        
        $this->page['goods'] = $this->_getGoods($page, $categories, $category);

        $this->page['categories'] = \Core\Model\Category::getAll();

        $this->page->display('goods/index.twig');

    }
    
    
    

    public function index($page = 1, $categories = false, $category = false, $query = false)
    {
        $this->page['selectedCategory'] = $this->cookieStorage->getValue('goods_selectedCategory') ?: 'all';
        

          if(isset($_POST['category_search'])) { 
            $query = $_POST['query'];
            $this->page['categories'] = \Core\Model\Category::all((array('conditions' => array('name like ? ', '%'.$query.'%' ))));
            $this->page['goods'] = \Core\Model\Good::all(array('conditions' => array('deleted is null')));
          }
          else { $this->page['categories'] = \Core\Model\Category::getAll();}
          
          if(isset($_POST['item_search'])) { 
          
            
            $query = $_POST['query'];

            $this->page['goods'] = \Core\Model\Good::all(array('conditions' => array('(name like ? or article like ? or description like ?) and deleted is null', '%'.$query.'%', '%'.$query.'%', '%'.$query.'%' ), 

             ));
            
          }
          else { 

            $total = \Core\Model\Good::all();

            $options['limit']  = \Core\Model\Good::$perPage;
            $options['offset'] = \Core\Model\Good::$perPage * ($page - 1);
            
            $this->page['pager']   = \Core\Model\Good::getPager($page, $category, 'manage_goods_index_page', $total);

            $this->page['goods'] = \Core\Model\Good::all(array('conditions' => array('deleted is null'), 
            'limit' => $options['limit'],
            'offset' => $options['offset']
             ));
          
          }

        $this->page->display('goods/index.twig');
    }
    

    
    
    public function import($values)  // Baltic IT adds
    {

       // Если файл загружен успешно, перемещаем его
       // из временной директории в конечную
       move_uploaded_file($_FILES["excel"]["tmp_name"], $_SERVER['DOCUMENT_ROOT']."/uploads/".$_FILES["excel"]["name"]);

      require_once $_SERVER['DOCUMENT_ROOT'].'/app/library/Classes/PHPExcel.php';
      require_once $_SERVER['DOCUMENT_ROOT'].'/app/library/Classes/PHPExcel/IOFactory.php';   
      
      if (!file_exists($_SERVER['DOCUMENT_ROOT']."/uploads/".$_FILES["excel"]["name"])) {
      	exit("Please run 05featuredemo.php first.\n");
      }
      
      $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
      $objPHPExcel = $objReader->load($_SERVER['DOCUMENT_ROOT']."/uploads/".$_FILES["excel"]["name"]);
      
      $error_pic = '';
      $error_category = '';
      $error_articul = '';
      foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
      
        $worksheetTitle = $worksheet->getTitle();
        $highestRow = $worksheet->getHighestRow(); // например, 10
        $highestColumn = $worksheet->getHighestColumn(); // например, 'F'
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $nrColumns = ord($highestColumn) - 64;
        
        for ($row = 2; $row <= $highestRow; ++ $row)
        {
        
          for ($col = 0; $col < $highestColumnIndex; ++ $col)
          {
            $cell = $worksheet->getCellByColumnAndRow($col, $row);
            $val = $cell->getValue();
            $dataType = \PHPExcel_Cell_DataType::dataTypeForValue($val);
            
            $data[$col]= $val;
          }
   
          unset($checkitems);
          if($data[0] == '0') {$good = new \Core\Model\Good(); $checkitems = \Core\Model\Good::all(array('conditions' => array('article = ?', $data[5]))); } 
          else { $good = \Core\Model\Good::find($data[0]); }
          
          $good->id               = $data[0];
          $good->name             = $data[1];
          $good->sex              = $data[2];
          $good->age_from         = $data[3];
          $good->age_to           = $data[4];
          $good->article          = $data[5];
          $good->p_price          = $data[6];
          $good->description      = $data[7];
          $good->meta_description = $data[8];
          $good->meta_keywords    = $data[9];
          $good->link             = $data[10];
          $good->discount         = $data[11];
          $good->discount_date    = $data[12];
          $good->brand_id         = $data[13];
          $good->compound         = $data[14];
          $good->is_available     = $data[22];
          $good->type_id          = $data[16];
          $good->sell_amount      = $data[20];
          $good->product_sku      = $data[21];
          $good->supplier_id      = $data[15];
          $good->in_stock         = $data[23];
          $good->sale             = $data[24];
          $good->title            = $data[25];
            
          if (empty($checkitems) )
          {
          
          if($good->is_available == '') { $good->is_available = '0';}
          
          if($good->article == '' or $good->product_sku == ''){
            $error_articul = $error_articul.', '.$good->name;
          }
          if($data[17] == ''){
            $error_category = $error_category.', '.$good->name;
          }
          
          
          
            $good->save();
          
          
          
          
          
          // PICTURES
          $pic_array = explode(",", $data[19]);

          foreach($pic_array as $key => $value) {
            if (trim($value) != '') {
              $cur_pic = explode("/", $value);

              //  ADD NEW
              
              if(empty($cur_pic[1])) {
                if (file_exists(ORIGINALS.'/'.$cur_pic[0])) {
                
                  unset($checkpics);
                  $checkpics = \Core\Model\Picture::all(array('conditions' => array('filename = ?', $cur_pic[0])));
                  if (empty($checkpics) )
                  {
                
                  $goodpicture = new \Core\Model\Goodpicture();
                  $picture = new \Core\Model\Picture();
  
                  CreateThumb(ORIGINALS.'/'.$cur_pic[0], '455', '455', UPLOADS."/full/".$cur_pic[0],'0');
                  CreateThumb(ORIGINALS.'/'.$cur_pic[0], '50', '50', UPLOADS."/icon/".$cur_pic[0],'1');
                  CreateThumb(ORIGINALS.'/'.$cur_pic[0], '150', '150', UPLOADS."/small/".$cur_pic[0],'0');
                  
                  $picture->filename = $cur_pic[0];
                  $picture->save();
                  
                  $goodpicture->picture_id = $picture->id;
                  $goodpicture->item_id = $good->id;
                  $goodpicture->save();
                  
                  }
                  
                }  
                else { 
                  $error_pic = $error_pic.', '.$good->name.':'.ORIGINALS.'/'.$cur_pic[0];
                }
              }
              
            }  
          
            
          }

          
          $category_array = explode(",", $data[17]);
          
          
          // CHECK AND DELETE
          $items = \Core\Model\Goodcategory::all(array('conditions' => array('good_id = ? ', $good->id)));
          
          foreach ($items as $item)
          {
              $check = '0';
              foreach($category_array as $key => $value) {
                if (trim($value) != '') {
                  if($value == $item->category_id) { $check = '1'; break; }
                }
              }
              
              if($check == '0') { $item->delete(); }
          }
          
          // CHECK AND ADD
          foreach($category_array as $key => $value) {
            if (trim($value) != '') {
            
              $items2 = \Core\Model\Goodcategory::all(array('conditions' => array('good_id = ? and category_id=?', $good->id, $value)));
            
              if (!count($items2))
              {       
                  $goodCategory = new \Core\Model\Goodcategory();
                  $goodCategory->good_id = $good->id;
                  $goodCategory->category_id = $value;
                  $goodCategory->save();
              }

            
            }
          }
          
          
          // SIZE ADD EDIT DELETE FROM EXCEL
          $size_array = explode(",", $data[18]);
          
          $toDelete = array();
          
          foreach($size_array as $key => $value) {
            if (trim($value) != '') {
            
              $cur_size = explode("/", $value);
              
              if ($cur_size[2]!= '')
              {
                  if($cur_size[0] == '0') {$size = new \Core\Model\Size(); } 
                  else { $size = \Core\Model\Size::find($cur_size[0]); }

                  $size->name = $cur_size[1];
                  $size->price = $cur_size[2];
                  $size->good_id = $good->id;
                  $size->save();
                  $dontDelete[] = $size->id;
              }

            
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
        
        }
      
      }
      
        if($error_pic != '' or $error_articul != '' or $error_category != '') {
          $page = \Core\Page::get();
        }
        
        if($error_pic != '') { $message_1 = "Изображение отсутствует!".$error_pic; $page->setMessage($message_1);} 
        else { $message_1 = '';}
        if($error_articul != '') { $message_2 = "Oтсутствует артикул!".$error_articul; $page->setMessage($message_2);} 
        else { $message_2 = '';}
        if($error_category != '') { $message_3 = "Oтсутствует категория!".$error_category; $page->setMessage($message_3);} 
        else { $message_3 = '';}
        

        $this->router->go($this->router->generate('manage_goods_exp'));
    }
    
    
    public function export($values)  // Baltic IT adds
    {
    
    
     //   error_reporting(E_ALL);
        
        /** PHPExcel */
        require_once $_SERVER['DOCUMENT_ROOT'].'/app/library/Classes/PHPExcel.php';
        
        /** PHPExcel_IOFactory */
        require_once $_SERVER['DOCUMENT_ROOT'].'/app/library/Classes/PHPExcel/IOFactory.php';    
        
        $this->page['selectedCategory'] = $this->cookieStorage->getValue('goods_selectedCategory') ?: 'all';
        $this->page['categories'] = \Core\Model\Category::getAll();
        //$this->page['goods'] = \Core\Model\Good::all(array('conditions' => array('deleted is null')));
        
        
        if($values['limit'] == '') { $values['limit'] = '1000';}
        if($values['start'] == '') { $values['start'] = '0';}
        
        $this->page['goods'] = \Core\Model\Good::all(array(
      				'conditions' => array('deleted is null'),
      				'order' => 'id',
      				'limit' => $values['limit'],
      				'offset'=> $values['start']

      			));
        
      
      // Create new PHPExcel object
      
      $objPHPExcel = new \PHPExcel();
      
      // Set properties
      $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
      					 ->setLastModifiedBy("Maarten Balliauw")
      					 ->setTitle("Office 2007 XLSX Test Document")
      					 ->setSubject("Office 2007 XLSX Test Document")
      					 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
      					 ->setKeywords("office 2007 openxml php")
      					 ->setCategory("Test result file");

      // Add some data
      $objPHPExcel->setActiveSheetIndex(0)
                  ->setCellValue('A1', 'id (если указать id = 0, то добавится новый товар)')
                  ->setCellValue('B1', 'name (Название товара)')
                  ->setCellValue('C1', 'sex (Пол)')
                  ->setCellValue('D1', 'age_from (Возраст от)')
                  ->setCellValue('E1', 'age_to (Возраст до)')
                  ->setCellValue('F1', 'article (Артикул товара)')
                  ->setCellValue('G1', 'p_price (Закупочная цена)')
                  ->setCellValue('H1', 'description (Описание товара)')
                  ->setCellValue('I1', 'meta_description (Мета описание)')
                  ->setCellValue('J1', 'meta_keywords (Мета ключевые слова)')
                  ->setCellValue('K1', 'link (Ссылка)')
                  ->setCellValue('L1', 'discount (Скидка %)')
                  ->setCellValue('M1', 'discount_date (Срок действая скидки)')
                  ->setCellValue('N1', 'brand_id (Производитель)')
                  ->setCellValue('O1', 'compound (Состав)')
                  ->setCellValue('P1', 'supplier (Поставщик)')
                  ->setCellValue('Q1', 'type_id')
                  ->setCellValue('R1', 'category (Категория)')
                  ->setCellValue('S1', 'id/name/price (если указать id = 0, то добавится новый размер)')
                  ->setCellValue('T1', 'id/filename (если указать только имя картинки то добавиться новая)')
                  ->setCellValue('U1', 'sell_amount (Кол-во проданных)')
                  ->setCellValue('V1', 'product_sku (Артикул производителя)')
                  ->setCellValue('W1', 'is_available (Видимость пользователю)')
                  ->setCellValue('X1', 'in_stock (Кол-во на складе)')
                  ->setCellValue('Y1', 'sale (Распродажа)')
                  ->setCellValue('Z1', 'title (Тайтл)');
                  
      
      $i = 1;
      foreach ($this->page['goods'] as $goods)
      {
        $i++;
  
  
        // ARRAY PICTURES
        
        $curPicArr = '';
        foreach ($goods->getIcons() as $ico) {
          $pics = \Core\Model\Picture::all(array('conditions' => array('id = ? ', $ico->picture_id)));
          foreach ($pics as $pic){
            $curPicArr = $curPicArr.$pic->id.'/'.$pic->filename.',';
          }
        }
        
        // ARRAY SIZE
        $curSizeArr = '';
        $sizes = \Core\Model\Size::all(array('conditions' => array('good_id=? and deleted IS NULL', $goods->id)));

        foreach ($sizes as $size) {
          $curSizeArr = $curSizeArr.$size->id.'/'.$size->name.'/'.$size->price.',';
        }
      	
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('0', $i, $goods->id); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('1', $i, $goods->name); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('2', $i, $goods->sex); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('3', $i, $goods->age_from); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('4', $i, $goods->age_to); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('5', $i, $goods->article); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('6', $i, $goods->p_price); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('7', $i, $goods->description); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('8', $i, $goods->meta_description); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('9', $i, $goods->meta_keywords); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('10', $i, $goods->link); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('11', $i, $goods->discount); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('12', $i, date("d-m-Y",strtotime($goods->discount_date)) ); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('13', $i, $goods->brand_id); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('14', $i, $goods->compound); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('15', $i, $goods->supplier_id); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('16', $i, $goods->type_id); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('17', $i, $goods->getCategories());
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('18', $i, $curSizeArr);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('19', $i, $curPicArr); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('20', $i, $goods->sell_amount); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('21', $i, $goods->product_sku); 
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('22', $i, $goods->is_available);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('23', $i, $goods->in_stock);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('24', $i, $goods->sale);
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow('25', $i, $goods->title);
      }

      // Rename sheet
      $objPHPExcel->getActiveSheet()->setTitle('Simple');
      
      // Set active sheet index to the first sheet, so Excel opens this as the first sheet
      $objPHPExcel->setActiveSheetIndex(0);
      
      // Redirect output to a client’s web browser (Excel2007)
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="01simple.xlsx"');
      header('Cache-Control: max-age=0');
      
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');
      exit;
     
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
       // $this->page['categories'] = \Core\Model\Category::all(array('conditions' => array('parent_id is null and NOT(id = ?)', $categoryId), 'order' => 'name'));
        $this->page['categories'] = \Core\Model\Category::getAll();
        
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
        $category->top_description  = $values['top_description'];
        $category->meta_description = $values['meta_description'];
        $category->meta_keywords    = $values['meta_keywords'];
        $category->title            = $values['title'];
        if(empty($values['title'])) { $category->title = $values['name']; }
        
        $category->link = $values['link'];
        
        $checkitems = \Core\Model\Category::all(array('conditions' => array('link = ? and id != ?', $category->link, $values['id'] )));
        if (!empty($checkitems) ) {
          $page = \Core\Page::get();
          $message = "У категории есть совпадения! Измените ссылку"; 
          $page->setMessage($message);
        }

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

    public function exp()  // Baltic IT adds
    {
        $this->_expform();
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
    
    private function _expform()
    {
        $page = $this->getPage();
        $page->display('goods/expform.twig');        
    }  
}
