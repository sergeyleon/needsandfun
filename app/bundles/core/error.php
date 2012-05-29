<?php

/**
 * Класс-обертка для ошибок из проекта
 *
 * @author Ilya Doroshin
 */
namespace Core;

class Error extends \Exception
{
   /**
    * показываем текущую ошибку
    */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    
    private function getPage()
    {
        $page = false;
        
        if (class_exists('\Core\Registry'))
        {
            $registry = \Core\Registry::get();
            $page = $registry->getPage();
        }
        
        return $page;
    }
    
    public function display()
    {
        $exception = array(
            'msg'   => $this->message,
            'file'  => $this->file,
            'line'  => $this->line,
            'trace' => $this->getTrace()
        );

        \Core\Engine::get()->getEnvironment()->set('default');                
        
        $page = $this->getPage();
        $page['exception'] = $exception;

        $page->display('errors/exception.twig');
    }
}