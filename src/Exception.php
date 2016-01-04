<?php
namespace Avenue;

use Avenue\App;
use Avenue\ExceptionInterface;

class Exception implements ExceptionInterface
{
    /**
     * Avenue class app instance.
     *
     * @var mixed
     */
    protected $app;
    
    /**
     * Exception class instance.
     *
     * @var mixed
     */
    protected $exc;
    
    /**
     * Exception class constructor.
     * 
     * @param App $app
     * @param \Exception $exc
     */
    public function __construct(App $app, \Exception $exc)
    {
        $this->app = $app;
        $this->exc = $exc;
    }
    
    /**
     * Triggering string magic method by printing out the object.
     */
    public function render()
    {
        echo $this;
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::__toString()
     */
    public function __toString()
    {
        $PATH_TO_EXCEPTION_FILE = __DIR__ . '/includes/exception_error.php';
        
        if (!file_exists($PATH_TO_EXCEPTION_FILE)) {
            die(sprintf('Exception view [%s] not found!', $PATH_TO_EXCEPTION_FILE));
        }
        
        ob_start();
        require_once $PATH_TO_EXCEPTION_FILE;
        
        $this->app->response->write('');
        $this->app->response->write(ob_get_clean());
        $this->app->response->render();
        
        exit(0);
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getMessage()
     */
    public function getMessage()
    {
        return $this->exc->getMessage();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getCode()
     */
    public function getCode()
    {
        return $this->exc->getCode();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getFile()
     */
    public function getFile()
    {
        return $this->exc->getFile();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getLine()
     */
    public function getLine()
    {
        return $this->exc->getLine();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getTrace()
     */
    public function getTrace()
    {
        return $this->exc->getTrace();
    }
    
    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getTraceAsString()
     */
    public function getTraceAsString()
    {
        return $this->exc->getTraceAsString();
    }
    
    /**
     * Return the actual exception class that triggered it.
     *
     * @return string
     */
    public function getExceptionClass()
    {
        return get_class($this->exc);
    }
}