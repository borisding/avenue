<?php
namespace Avenue;

use Exception as BaseException;
use Avenue\App;
use Avenue\Interfaces\ExceptionInterface;

class Exception implements ExceptionInterface
{
    /**
     * Avenue class app instance.
     *
     * @var \Avenue\App
     */
    protected $app;

    /**
     * Exception class instance.
     *
     * @var \Exception
     */
    protected $exception;

    /**
     * Exception class constructor.
     *
     * @param App $app
     * @param BaseException $exception
     */
    public function __construct(App $app, BaseException $exception)
    {
        $this->app = $app;
        $this->exception = $exception;
    }

    /**
     * Triggering string magic method by printing out the object.
     *
     * @return \Avenue\Exception
     */
    public function render()
    {
        error_reporting(0);
        echo $this;
    }

    /**
     * Rendering the caught exception output.
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        $exceptionErrorFile = __DIR__ . '/_includes/exception_error.php';

        if (!file_exists($exceptionErrorFile)) {
            die(sprintf('Exception view [%s] not found!', $exceptionErrorFile));
        }

        require_once $exceptionErrorFile;

        $response = $this->app->response();
        $response->write('');
        $response->write(ob_get_clean());

        return $response->render();
    }

    /**
     * Return the base Exception class instance.
     *
     * @return Exception
     */
    public function getBaseInstance()
    {
        return $this->exception;
    }
    
    /**
     * Get exception message.
     *
     * @return mixed
     */
    public function getMessage()
    {
        return $this->exception->getMessage();
    }

    /**
     * Get exception code.
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->exception->getCode();
    }

    /**
     * Get the source filename of exception.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->exception->getFile();
    }

    /**
     * Get the source line of exception.
     *
     * @return integer
     */
    public function getLine()
    {
        return $this->exception->getLine();
    }

    /**
     * Get array of the backtrace.
     *
     * @return array
     */
    public function getTrace()
    {
        return $this->exception->getTrace();
    }

    /**
     * Get formatted string of trace.
     *
     * @return string
     */
    public function getTraceAsString()
    {
        return $this->exception->getTraceAsString();
    }

    /**
     * Return the actual exception class that triggered it.
     *
     * @return string
     */
    public function getExceptionClass()
    {
        return get_class($this->exception);
    }

    /**
     * Return current app ID.
     */
    public function getAppId()
    {
       return $this->app->getId();
    }
}
