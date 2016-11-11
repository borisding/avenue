<?php
namespace Avenue;

use Avenue\App;
use Avenue\Interfaces\ExceptionInterface;
use Exception as CoreException;

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
     * @param CoreException $exception
     */
    public function __construct(App $app, CoreException $exception)
    {
        $this->app = $app;
        $this->exception = $exception;
    }

    /**
     * Triggering string magic method by printing out the object.
     *
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::render()
     */
    public function render()
    {
        error_reporting(0);
        echo $this;
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::__toString()
     */
    public function __toString()
    {
        ob_start();
        $response = $this->app->response();
        $exceptionErrorFile = __DIR__ . '/_includes/exception_error.php';

        if (!file_exists($exceptionErrorFile)) {
            die(sprintf('Exception view [%s] not found!', $exceptionErrorFile));
        }

        require_once $exceptionErrorFile;

        $response->write('');
        $response->write(ob_get_clean());

        return $response->render();
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getMessage()
     */
    public function getMessage()
    {
        return $this->exception->getMessage();
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getCode()
     */
    public function getCode()
    {
        return $this->exception->getCode();
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getFile()
     */
    public function getFile()
    {
        return $this->exception->getFile();
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getLine()
     */
    public function getLine()
    {
        return $this->exception->getLine();
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getTrace()
     */
    public function getTrace()
    {
        return $this->exception->getTrace();
    }

    /**
     * {@inheritDoc}
     * @see \Avenue\Interfaces\ExceptionInterface::getTraceAsString()
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