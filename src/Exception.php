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
     * @var mixed
     */
    protected $app;

    /**
     * Exception class instance.
     *
     * @var mixed
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
     * Decide response status code based on the exception code.
     * Triggering string magic method by printing out the object.
     */
    public function render()
    {
        error_reporting(0);

        $code = $this->getCode();

        if (!is_int($code) || $code < 400 || $code > 599) {
            $code = 500;
        }

        $this->app->response->withStatus($code);
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
     * Get the matched rule regular expression.
     */
    public function getRouteMatchedRuleRegexp()
    {
       return $this->app->route->getMatchedRuleRegexp();
    }
}