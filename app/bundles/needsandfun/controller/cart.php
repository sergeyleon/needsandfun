<?php

namespace Needsandfun\Controller;

class Cart extends \Core\Abstracts\Authorized
{
    public function index()
    {   
    
        
        if (isset($_POST['cartSubmit']))
        {
            $this->_proceed($_POST);
        }
        else 
        {   
            $this->page['client'] = $this->getClient();
            
            if ($this->getUser())
            {
                $this->page['currentEmail'] = $this->getUser()->login;
            }

            if ($this->getClient())
            {
                $this->page['currentPhone'] = $this->getClient()->phone;
            }
            
            if ($this->getClient())
            {
                $this->page['currentName'] = $this->getClient()->first_name.' '.$this->getClient()->last_name;
            }

            $this->page['shopCategories'] = \Core\Model\Category::getAll();
            
            $this->page['cartDefaults'] = array(
                'pickupAddress' => 'Москва, м. Третьяковская, Б. Толмачевский пер., д. 5, корп. 1, территория института "Гиредмет". Перед приездом за заказом не забудьте убедиться в том, что товар у нас в офисе :)',
                'metrolines'        => \Core\Model\Metroline::all(array('order' => 'name'))
            );


            $this->page['cartDeliveries'] = array(
                'courier' => array(
                    'price' => 300,
                    'text'  => 'Доставка курьером'
                ),
                'metro' => array(
                    'price' => 150,
                    'text'  => 'Доставка к ближайшему метро'
                ),
                'pickup' => array(
                    'price' => 0,
                    'text'  => 'Самовывоз'
                ),
                'ems' => array(
   
                    'text'  => 'Доставка почтой'
                ),
            );
            
            // YA FAST ORDER BUTTON PARAMETRS
            if(isset($_POST['operation_id'])) {
              $this->page['currentCity'] = $_POST['city'];
              $this->page['currentEmail'] = $_POST['email'];
              $this->page['currentPhone'] = $_POST['phone'];
              $this->page['currentName'] = $_POST['firstname'].' '.$_POST['lastname'].' '.$_POST['fathersname'];
              $this->page['currentStreet'] = $_POST['street'];
              $this->page['currentHouse'] = $_POST['building'];
              $this->page['currentBuilding'] = $_POST['suite'];
              $this->page['currentAppartments'] = $_POST['flat'];
              $this->page['currentComment'] = $_POST['comment'];
            }

            $this->page->display('shop/cart.twig');
        }
    }
    
    public function success() {
      $this->page->display('shop/cart_success.twig');
    }
    
    public function ems_success() {
      $this->page->display('shop/ems_success.twig');
    }
    
    private function _proceed($values) 
    {
        
        
        $cart = \Core\Model\Cart::init($this->sessionStorage);
        $order = $cart->confirm($values);
        
        $delivery = \Core\Model\Delivery::all(array('conditions' => array('order_id = ?', $order->id)));
        
        $metro = \Core\Model\Metro::all(array('conditions' => array('id = ?', $delivery[0]->metro_id )));

        if ($order)
        {
            $now = new \DateTime();


            $fileAttach = '';
            if($values['deliveryType'] == 'ems') {
              require_once $_SERVER['DOCUMENT_ROOT'].'/app/library/Classes/PHPExcel/IOFactory.php';    
              $objPHPExcel = \PHPExcel_IOFactory::load($_SERVER['DOCUMENT_ROOT']."/uploads/claim-check.xlsx");
      
      
              $summ = $delivery[0]->delivery_price+$order->price-$order->discount;
              
              $fio = $values['delivery']['ems']['name'];
              if($order->getClient()->first_name != null and $order->getClient()->last_name != '') {
                $fio = $order->getClient()->first_name.' '.$order->getClient()->last_name;
              }
              
              
              // Add some data
              $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('E11', 'Оплата заказа '.$order->id.' за игрушки')
                ->setCellValue('N13', $fio)
                ->setCellValue('N14', $delivery[0]->address)
                ->setCellValue('L15', $summ	)
                ->setCellValue('E31', 'Оплата заказа '.$order->id.' за игрушки')
                ->setCellValue('N33', $fio)
                ->setCellValue('N34', $delivery[0]->address)
                ->setCellValue('L35', $summ	);
                
              require_once $_SERVER['DOCUMENT_ROOT'].'/app/library/Classes/PHPExcel/Writer/Excel2007.php';
              $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
              $objWriter->save($_SERVER['DOCUMENT_ROOT']."/uploads/check.xlsx");
              $fileAttach = $_SERVER['DOCUMENT_ROOT']."/uploads/check.xlsx";
              
              $this->page['check'] = $fileAttach;
            }
            
            
            $options = array(
                'orderId'  => $order->id,
                'date'     => $now,
                'delivery' => $delivery[0],
                'metro'    => $metro[0]->name,
                'email'    => $order->getClient()->getUser()->login,
                'file'     => $fileAttach
            );
            
            Email::confirmOrder($order, $options);
            
            Email::confirmOrderAdmin($order, $options);

            $this->page->setMessage('Заказ оформлен успешно! В ближайшее время наши менеджеры свяжутся с вами для подтверждения заказа.');

            
            if($values['deliveryType'] == 'ems') {
              $this->router->go($this->router->generate('ems_success'));
            }
            else {
              $this->router->go($this->router->generate('shop_success'));
            }
 
        }
        else
        {
            $this->router->reload();
        }
    }
    
    public function update()
    {
        if ('addGood' == $_POST['action'])
        {
            $this->_addGood($_POST['good'], $_POST['qnt']);
        }
        else if ('removeGood' == $_POST['action'])
        {
            $this->_removeGood($_POST['good'], $_POST['qnt']);
        }        
    }
    
    private function _addGood($good, $qnt = 1)
    {
        \Core\Model\Cart::init($this->sessionStorage)->addGood($good, $qnt);
    }
    
    private function _removeGood($good, $qnt = 1)
    {
        \Core\Model\Cart::init($this->sessionStorage)->removeGood($good, $qnt);
    }
    
}