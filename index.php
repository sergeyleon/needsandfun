<?php

// Baltic It redirect old -> new page
$_URL = preg_replace("/^(.*?)index\.php$/is", "$1", $_SERVER['SCRIPT_NAME']);  
$_URL = preg_replace("/^".preg_quote($_URL, "/")."/is", "", urldecode($_SERVER['REQUEST_URI']));  
$_URL = preg_replace("/(\/?)(\?.*)?$/is", "", $_URL);  
$_URL = preg_replace("/[^0-9A-Za-z._\\-\\/]/is", "", $_URL);	// вырезаем ненужные символы (не обязательно это делать)
$_URL = mysql_escape_string($_URL); // экранирует спец символы
$_URL = trim($_URL);				// удаление пробельных символов
$_URLs = explode("-", $_URL);




if(@$_URLs[1] != '') {
  switch ($_URLs[0]) 
  {  
  	case 'store':
  		$_URL = str_replace("store-", "", $_URL);
  		header("HTTP/1.1 301 Moved Permanently");
  		header('Location: http://'.$_SERVER['HTTP_HOST'].'/store/'.$_URL.'/');
      exit;
  	break;
  	case 'good':
  		$_URL = str_replace("good-", "", $_URL);
  		header("HTTP/1.1 301 Moved Permanently");
  		header('Location: http://'.$_SERVER['HTTP_HOST'].'/good/'.$_URL.'/');
      exit;
  	break;
  }	
}
// End Baltic It

require_once 'app/bootstrap.php';


